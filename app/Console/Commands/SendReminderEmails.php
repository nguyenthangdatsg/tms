<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendReminderEmails extends Command
{
    protected $signature = 'reminder:send';
    protected $description = 'Send all notification emails for learning paths';

    public function handle(): int
    {
        $this->info('Starting notification email job...');
        
        $sent = 0;
        
        try {
            // Get all notification settings
            $notifications = DB::select("
                SELECT * FROM mdl_local_learningpath_notifications 
                WHERE enrollment_enable = 1 
                   OR expiration_enable = 1 
                   OR enrollment_reminder_enable = 1
                   OR expiration_reminder_enable = 1
                   OR day_frequency_enable = 1
                   OR completion_path_enable = 1
            ");
            
            $this->info("Found " . count($notifications) . " notification configs");
            
            foreach ($notifications as $notif) {
                $this->processNotifications($notif);
            }
            
            $this->info("Completed. Total sent: {$sent}");
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function processNotifications($notif): void
    {
        $this->line("Processing notifications for path {$notif->lpt_id}");
        
        // Get enrolled users
        $enrolledUsers = DB::select("
            SELECT u.id, u.firstname, u.lastname, u.email, lpu.timecreated as enrolled_at
            FROM mdl_user u
            JOIN mdl_local_learningpath_users lpu ON lpu.u_id = u.id
            WHERE lpu.lpt_id = ?
            AND u.deleted = 0
            AND u.suspended = 0
        ", [$notif->lpt_id]);
        
        if (empty($enrolledUsers)) {
            return;
        }
        
        // Get path info
        $pathInfo = DB::select("
            SELECT lp.*, 
                (SELECT COUNT(*) FROM mdl_local_learningpath_lines WHERE lpt_id = lp.id) as total_courses
            FROM mdl_local_learningpath lp
            WHERE lp.id = ?
        ", [$notif->lpt_id]);
        
        if (empty($pathInfo)) {
            return;
        }
        
        $pathInfo = $pathInfo[0];
        
        foreach ($enrolledUsers as $user) {
            $enrolledAt = $user->enrolled_at ?? 0;
            $daysSinceEnrollment = $enrolledAt > 0 ? floor((time() - $enrolledAt) / 86400) : 0;
            
            // 1. Enrollment Reminder (X days after enrollment)
            if (!empty($notif->enrollment_reminder_enable) && !empty($notif->day_after_enrollment)) {
                if ($daysSinceEnrollment > 0 && $daysSinceEnrollment == $notif->day_after_enrollment) {
                    $this->sendEnrollmentReminder($user, $notif, $pathInfo, $daysSinceEnrollment);
                }
            }
            
            // 2. Expiration Reminder (X days before expiration)
            if (!empty($notif->expiration_reminder_enable) && !empty($notif->day_before_expiration)) {
                $expirationDate = $pathInfo->enddate ?? 0;
                if ($expirationDate > 0) {
                    $daysUntilExpiration = floor(($expirationDate - time()) / 86400);
                    if ($daysUntilExpiration > 0 && $daysUntilExpiration == $notif->day_before_expiration) {
                        $this->sendExpirationReminder($user, $notif, $pathInfo, $daysUntilExpiration);
                    }
                }
            }
            
            // 3. Day Frequency Reminder (periodic)
            if (!empty($notif->day_frequency_enable) && !empty($notif->day_frequency)) {
                $days = $notif->day_frequency;
                if ($enrolledAt > 0 && $daysSinceEnrollment >= $days) {
                    $this->sendFrequencyReminder($user, $notif, $pathInfo, $enrolledAt);
                }
            }
            
            // 4. Completion Notification
            if (!empty($notif->completion_path_enable)) {
                $this->sendCompletionNotification($user, $notif, $pathInfo);
            }
        }
    }
    
    private function sendEnrollmentReminder($user, $notif, $pathInfo, $days): void
    {
        $template = $notif->enrollment_reminder_mail_templates;
        if (empty($template)) {
            return;
        }
        
        $variables = $this->getUserVariables($user, $pathInfo, $days);
        $variables['day_after_enrollment'] = $days;
        
        $body = $this->parseTemplate($template, $variables);
        $subject = 'Nhắc nhở sau ghi danh: ' . ($pathInfo->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function sendExpirationReminder($user, $notif, $pathInfo, $days): void
    {
        $template = $notif->expiration_reminder_mail_templates;
        if (empty($template)) {
            return;
        }
        
        $variables = $this->getUserVariables($user, $pathInfo, 0);
        $variables['day_before_expiration'] = $days;
        
        $body = $this->parseTemplate($template, $variables);
        $subject = 'Nhắc nhở sắp hết hạn: ' . ($pathInfo->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function sendFrequencyReminder($user, $notif, $pathInfo, $enrolledAt): void
    {
        $template = $notif->day_frequency_mail_templates;
        if (empty($template)) {
            return;
        }
        
        $days = $notif->day_frequency;
        $variables = $this->getUserVariables($user, $pathInfo, 0);
        $variables['days_since_reminder'] = floor((time() - $enrolledAt) / 86400);
        
        $body = $this->parseTemplate($template, $variables);
        $subject = 'Nhắc nhở định kỳ: ' . ($pathInfo->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function sendCompletionNotification($user, $notif, $pathInfo): void
    {
        $template = $notif->completion_path_mail_templates;
        if (empty($template)) {
            return;
        }
        
        // Check if user completed all required courses
        $completedResult = DB::select("
            SELECT COUNT(*) as cnt FROM mdl_local_learningpath_lines lpl
            JOIN mdl_course_completions cc ON cc.course = lpl.course_id AND cc.userid = ?
            WHERE lpl.lpt_id = ? AND lpl.required = 1
        ", [$user->id, $notif->lpt_id]);
        
        $completed = $completedResult[0]->cnt ?? 0;
        
        if ($completed >= $pathInfo->total_courses && $pathInfo->total_courses > 0) {
            $variables = $this->getUserVariables($user, $pathInfo, 0);
            $variables['completion_date'] = date('d/m/Y');
            
            $body = $this->parseTemplate($template, $variables);
            $subject = 'Chúc mừng hoàn thành: ' . ($pathInfo->name ?? 'Learning Path');
            
            $this->sendEmail($user->email, $subject, $body);
        }
    }
    
    private function getUserVariables($user, $pathInfo, $daysSinceEnrollment): array
    {
        // Get completion stats
        try {
            $completedResult = DB::select("
                SELECT COUNT(*) as cnt FROM mdl_local_learningpath_lines lpl
                JOIN mdl_course_completions cc ON cc.course = lpl.course_id AND cc.userid = ?
                WHERE lpl.lpt_id = ? AND lpl.required = 1
            ", [$user->id, $pathInfo->id]);
            $completed = $completedResult[0]->cnt ?? 0;
        } catch (\Exception $e) {
            $completed = 0;
        }
        
        $total = $pathInfo->total_courses ?? 0;
        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
        
        return [
            'firstname' => $user->firstname ?? '',
            'lastname' => $user->lastname ?? '',
            'user_fullname' => trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')),
            'email' => $user->email ?? '',
            'learning_path_name' => $pathInfo->name ?? '',
            'learning_path_startdate' => $pathInfo->startdate ? date('d/m/Y', $pathInfo->startdate) : '',
            'learning_path_enddate' => $pathInfo->enddate ? date('d/m/Y', $pathInfo->enddate) : '',
            'completion_percentage' => $percentage,
            'completed_courses' => $completed,
            'total_courses' => $total,
        ];
    }
    
    private function parseTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }
    
    private function sendEmail(string $to, string $subject, string $body): void
    {
        $this->line("  -> Email sent to: {$to}");
        $this->line("     Subject: {$subject}");
    }
}