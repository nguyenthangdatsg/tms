<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestNotifications extends Command
{
    protected $signature = 'notification:test {type=all} {lpt_id=1} {user_id=1}';
    protected $description = 'Test notification emails for learning paths';

    public function handle(): int
    {
        $type = $this->argument('type');
        $lptId = (int) $this->argument('lpt_id');
        $userId = (int) $this->argument('user_id');
        
        $this->info("Testing notification: {$type} for path {$lptId}, user {$userId}");
        $this->info(str_repeat('-', 50));
        
        switch ($type) {
            case 'enrollment':
                $this->testEnrollmentNotification($lptId, $userId);
                break;
            case 'enrollment_reminder':
                $this->testEnrollmentReminder($lptId, $userId);
                break;
            case 'expiration':
                $this->testExpirationNotification($lptId, $userId);
                break;
            case 'expiration_reminder':
                $this->testExpirationReminder($lptId, $userId);
                break;
            case 'frequency':
            case 'day_frequency':
                $this->testFrequencyReminder($lptId, $userId);
                break;
            case 'completion':
                $this->testCompletionNotification($lptId, $userId);
                break;
            case 'all':
            default:
                $this->testAllNotifications($lptId, $userId);
                break;
        }
        
        return Command::SUCCESS;
    }
    
    private function testEnrollmentNotification(int $lptId, int $userId): void
    {
        $this->info("Testing ENROLLMENT NOTIFICATION...");
        
        $notif = DB::select("SELECT * FROM mdl_local_learningpath_notifications WHERE lpt_id = ? AND enrollment_enable = 1", [$lptId]);
        
        if (empty($notif)) {
            $this->warn("No enrollment notification enabled for path {$lptId}");
            return;
        }
        
        $notif = $notif[0];
        $user = $this->getUser($userId);
        $path = $this->getPath($lptId);
        
        $template = $notif->enrollment_mail_templates;
        $variables = $this->getUserVariables($user, $path, $userId);
        
        $body = $this->parseTemplate($template, $variables);
        $subject = 'Thông báo ghi danh: ' . ($path->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function testEnrollmentReminder(int $lptId, int $userId): void
    {
        $this->info("Testing ENROLLMENT REMINDER...");
        
        $notif = DB::select("SELECT * FROM mdl_local_learningpath_notifications WHERE lpt_id = ? AND enrollment_reminder_enable = 1", [$lptId]);
        
        if (empty($notif)) {
            $this->warn("No enrollment reminder enabled for path {$lptId}");
            return;
        }
        
        $notif = $notif[0];
        $user = $this->getUser($userId);
        $path = $this->getPath($lptId);
        
        // Get user's enrollment date
        $enrolled = DB::select("SELECT timecreated FROM mdl_local_learningpath_users WHERE lpt_id = ? AND u_id = ?", [$lptId, $userId]);
        $enrolledAt = $enrolled[0]->timecreated ?? time();
        $daysSince = floor((time() - $enrolledAt) / 86400);
        
        $template = $notif->enrollment_reminder_mail_templates;
        $variables = $this->getUserVariables($user, $path, $userId);
        $variables['day_after_enrollment'] = $daysSince;
        
        $body = $this->parseTemplate($template, $variables);
        
        // Override template with test day value
        $body = str_replace('{{day_after_enrollment}}', $notif->day_after_enrollment, $body);
        
        $subject = 'Nhắc nhở sau ghi danh: ' . ($path->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function testExpirationNotification(int $lptId, int $userId): void
    {
        $this->info("Testing EXPIRATION NOTIFICATION...");
        
        $notif = DB::select("SELECT * FROM mdl_local_learningpath_notifications WHERE lpt_id = ? AND expiration_enable = 1", [$lptId]);
        
        if (empty($notif)) {
            $this->warn("No expiration notification enabled for path {$lptId}");
            return;
        }
        
        $notif = $notif[0];
        $user = $this->getUser($userId);
        $path = $this->getPath($lptId);
        
        $template = $notif->expiration_mail_templates;
        $variables = $this->getUserVariables($user, $path, $userId);
        
        $body = $this->parseTemplate($template, $variables);
        $subject = 'Thông báo hết hạn: ' . ($path->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function testExpirationReminder(int $lptId, int $userId): void
    {
        $this->info("Testing EXPIRATION REMINDER...");
        
        $notif = DB::select("SELECT * FROM mdl_local_learningpath_notifications WHERE lpt_id = ? AND expiration_reminder_enable = 1", [$lptId]);
        
        if (empty($notif)) {
            $this->warn("No expiration reminder enabled for path {$lptId}");
            return;
        }
        
        $notif = $notif[0];
        $user = $this->getUser($userId);
        $path = $this->getPath($lptId);
        
        $template = $notif->expiration_reminder_mail_templates;
        $variables = $this->getUserVariables($user, $path, $userId);
        $variables['day_before_expiration'] = $notif->day_before_expiration;
        
        $body = $this->parseTemplate($template, $variables);
        $subject = 'Nhắc nhở sắp hết hạn: ' . ($path->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function testFrequencyReminder(int $lptId, int $userId): void
    {
        $this->info("Testing DAY FREQUENCY REMINDER...");
        
        $notif = DB::select("SELECT * FROM mdl_local_learningpath_notifications WHERE lpt_id = ? AND day_frequency_enable = 1", [$lptId]);
        
        if (empty($notif)) {
            $this->warn("No frequency reminder enabled for path {$lptId}");
            return;
        }
        
        $notif = $notif[0];
        $user = $this->getUser($userId);
        $path = $this->getPath($lptId);
        
        $template = $notif->day_frequency_mail_templates;
        $variables = $this->getUserVariables($user, $path, $userId);
        
        $body = $this->parseTemplate($template, $variables);
        $subject = 'Nhắc nhở định kỳ: ' . ($path->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function testCompletionNotification(int $lptId, int $userId): void
    {
        $this->info("Testing COMPLETION NOTIFICATION...");
        
        $notif = DB::select("SELECT * FROM mdl_local_learningpath_notifications WHERE lpt_id = ? AND completion_path_enable = 1", [$lptId]);
        
        if (empty($notif)) {
            $this->warn("No completion notification enabled for path {$lptId}");
            return;
        }
        
        $notif = $notif[0];
        $user = $this->getUser($userId);
        $path = $this->getPath($lptId);
        
        $template = $notif->completion_path_mail_templates;
        $variables = $this->getUserVariables($user, $path, $userId);
        $variables['completion_date'] = date('d/m/Y');
        
        $body = $this->parseTemplate($template, $variables);
        $subject = 'Chúc mừng hoàn thành: ' . ($path->name ?? 'Learning Path');
        
        $this->sendEmail($user->email, $subject, $body);
    }
    
    private function testAllNotifications(int $lptId, int $userId): void
    {
        $this->testEnrollmentNotification($lptId, $userId);
        $this->testEnrollmentReminder($lptId, $userId);
        $this->testExpirationNotification($lptId, $userId);
        $this->testExpirationReminder($lptId, $userId);
        $this->testFrequencyReminder($lptId, $userId);
        $this->testCompletionNotification($lptId, $userId);
    }
    
    private function getUser(int $userId): object
    {
        $user = DB::select("SELECT * FROM mdl_user WHERE id = ?", [$userId]);
        if (empty($user)) {
            throw new \Exception("User {$userId} not found");
        }
        return $user[0];
    }
    
    private function getPath(int $lptId): object
    {
        $path = DB::select("SELECT * FROM mdl_local_learningpath WHERE id = ?", [$lptId]);
        if (empty($path)) {
            throw new \Exception("Learning path {$lptId} not found");
        }
        return $path[0];
    }
    
    private function getUserVariables($user, $path, int $userId): array
    {
        $completedResult = DB::select("
            SELECT COUNT(*) as cnt FROM mdl_local_learningpath_lines lpl
            JOIN mdl_course_completions cc ON cc.course = lpl.course_id AND cc.userid = ?
            WHERE lpl.lpt_id = ? AND lpl.required = 1
        ", [$userId, $path->id]);
        
        $completed = $completedResult[0]->cnt ?? 0;
        $total = $path->total_courses ?? 0;
        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
        
        return [
            'firstname' => $user->firstname ?? '',
            'lastname' => $user->lastname ?? '',
            'user_fullname' => trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')),
            'email' => $user->email ?? '',
            'learning_path_name' => $path->name ?? '',
            'learning_path_startdate' => $path->startdate ? date('d/m/Y', $path->startdate) : '',
            'learning_path_enddate' => $path->enddate ? date('d/m/Y', $path->enddate) : '',
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
        $this->line("TO: {$to}");
        $this->line("Subject: {$subject}");
        $this->line("Body preview: " . substr(strip_tags($body), 0, 100) . "...");
        $this->line("");
    }
}