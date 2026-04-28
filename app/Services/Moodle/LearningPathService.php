<?php

namespace App\Services\Moodle;

use Illuminate\Support\Facades\DB;

class LearningPathService
{
    public static function getLearningPaths(): array
    {
        return DB::select("
            SELECT lp.*,
                (SELECT COUNT(*) FROM mdl_local_learningpath_users WHERE lpt_id = lp.id) as enrolled_count
            FROM mdl_local_learningpath lp
            ORDER BY id ASC
        ");
    }

    public static function getLearningPath(int $id): ?object
    {
        $results = DB::select("
            SELECT * FROM mdl_local_learningpath WHERE id = ?
        ", [$id]);
        return $results[0] ?? null;
    }

    public static function createLearningPath(array $data): int
    {
        $time = time();
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $startdate = !empty($data['startdate']) ? strtotime($data['startdate']) : 0;
        $enddate = !empty($data['enddate']) ? strtotime($data['enddate']) : 0;
        $credit = $data['credit'] ?? 0;
        $published = $data['published'] ?? 0;

        DB::insert("
            INSERT INTO mdl_local_learningpath
            (name, description, credit, startdate, enddate, published, timecreated, timemodified)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [$name, $description, $credit, $startdate, $enddate, $published, $time, $time]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        return $result[0]->id;
    }

    public static function updateLearningPath(int $id, array $data): bool
    {
        $sets = [];
        $values = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            if (in_array($key, ['startdate', 'enddate']) && !empty($value)) {
                $values[] = strtotime($value);
            } else {
                $values[] = $value;
            }
        }

        DB::update("
            UPDATE mdl_local_learningpath
            SET " . implode(', ', $sets) . ", timemodified = ?
            WHERE id = ?
        ", array_merge($values, [time(), $id]));

        return true;
    }

    public static function deleteLearningPath(int $id): bool
    {
        DB::delete('DELETE FROM mdl_local_learningpath_lines WHERE lpt_id = ?', [$id]);
        DB::delete('DELETE FROM mdl_local_learningpath_users WHERE lpt_id = ?', [$id]);
        DB::delete('DELETE FROM mdl_local_learningpath_cohorts WHERE lpt_id = ?', [$id]);
        DB::delete('DELETE FROM mdl_local_learningpath_notifications WHERE lpt_id = ?', [$id]);
        DB::delete('DELETE FROM mdl_local_learningpath WHERE id = ?', [$id]);
        return true;
    }

    public static function getLearningPathNotifications(int $lptId): ?object
    {
        $results = DB::select("
            SELECT * FROM mdl_local_learningpath_notifications WHERE lpt_id = ?
        ", [$lptId]);
        return $results[0] ?? null;
    }

    public static function saveLearningPathNotifications(int $lptId, array $data): bool
    {
        $existing = self::getLearningPathNotifications($lptId);

        if ($existing) {
            DB::update("
                UPDATE mdl_local_learningpath_notifications SET
                    enrollment_enable = ?,
                    enrollment_mail_templates = ?,
                    expiration_enable = ?,
                    expiration_mail_templates = ?,
                    enrollment_reminder_enable = ?,
                    day_after_enrollment = ?,
                    enrollment_reminder_mail_templates = ?,
                    expiration_reminder_enable = ?,
                    day_before_expiration = ?,
                    expiration_reminder_mail_templates = ?,
                    day_frequency_enable = ?,
                    day_frequency = ?,
                    day_frequency_mail_templates = ?,
                    completion_path_enable = ?,
                    completion_path_mail_templates = ?
                WHERE lpt_id = ?
            ", [
                $data['enrollment_enable'] ?? 0,
                $data['enrollment_mail_templates'] ?? '',
                $data['expiration_enable'] ?? 0,
                $data['expiration_mail_templates'] ?? '',
                $data['enrollment_reminder_enable'] ?? 0,
                $data['day_after_enrollment'] ?? 7,
                $data['enrollment_reminder_mail_templates'] ?? '',
                $data['expiration_reminder_enable'] ?? 0,
                $data['day_before_expiration'] ?? 7,
                $data['expiration_reminder_mail_templates'] ?? '',
                $data['day_frequency_enable'] ?? 0,
                $data['day_frequency'] ?? 7,
                $data['day_frequency_mail_templates'] ?? '',
                $data['completion_path_enable'] ?? 0,
                $data['completion_path_mail_templates'] ?? '',
                $lptId
            ]);
        } else {
            DB::insert("
                INSERT INTO mdl_local_learningpath_notifications
                (lpt_id, enrollment_enable, enrollment_mail_templates, expiration_enable, expiration_mail_templates,
                enrollment_reminder_enable, day_after_enrollment, enrollment_reminder_mail_templates,
                expiration_reminder_enable, day_before_expiration, expiration_reminder_mail_templates,
                day_frequency_enable, day_frequency, day_frequency_mail_templates,
                completion_path_enable, completion_path_mail_templates)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $lptId,
                $data['enrollment_enable'] ?? 0,
                $data['enrollment_mail_templates'] ?? '',
                $data['expiration_enable'] ?? 0,
                $data['expiration_mail_templates'] ?? '',
                $data['enrollment_reminder_enable'] ?? 0,
                $data['day_after_enrollment'] ?? 7,
                $data['enrollment_reminder_mail_templates'] ?? '',
                $data['expiration_reminder_enable'] ?? 0,
                $data['day_before_expiration'] ?? 7,
                $data['expiration_reminder_mail_templates'] ?? '',
                $data['day_frequency_enable'] ?? 0,
                $data['day_frequency'] ?? 7,
                $data['day_frequency_mail_templates'] ?? '',
                $data['completion_path_enable'] ?? 0,
                $data['completion_path_mail_templates'] ?? ''
            ]);
        }
        return true;
    }

    public static function getLearningPathCourses(int $lptId): array
    {
        return DB::select("
            SELECT lpl.*,
                c.fullname,
                c.shortname,
                lc.code as catalogue_code,
                lc.name as catalogue_name
            FROM mdl_local_learningpath_lines lpl
            LEFT JOIN mdl_course c ON lpl.course_id = c.id AND lpl.course_type = 'moodle'
            LEFT JOIN mdl_local_catalogue_courses lc ON lpl.catalogue_code = lc.code AND lpl.course_type = 'catalogue'
            WHERE lpl.lpt_id = ?
            ORDER BY lpl.sortorder ASC
        ", [$lptId]);
    }

    public static function getLearningPathAvailableCourses(): array
    {
        return DB::select("
            SELECT c.id, c.fullname, c.shortname, c.idnumber, 'moodle' as course_type, NULL as catalogue_code
            FROM mdl_course c
            WHERE c.id > 1 AND c.visible = 1
            ORDER BY c.fullname
        ");
    }

    public static function getLearningPathAvailableCatalogueCourses(): array
    {
        return DB::select("
            SELECT id, code, name as fullname, name as shortname, 'catalogue' as course_type
            FROM mdl_local_catalogue_courses
            WHERE visible = 1
            ORDER BY name
        ");
    }

    public static function addMoodleCourseToLearningPath(int $lptId, int $courseId, int $sortorder = 0): bool
    {
        DB::insert("
            INSERT INTO mdl_local_learningpath_lines (lpt_id, course_id, course_type, sortorder, required)
            VALUES (?, ?, 'moodle', ?, 1)
        ", [$lptId, $courseId, $sortorder]);
        return true;
    }

    public static function addCatalogueCourseToLearningPath(int $lptId, string $catalogueCode, int $sortorder = 0): bool
    {
        DB::insert("
            INSERT INTO mdl_local_learningpath_lines (lpt_id, course_type, catalogue_code, sortorder, required)
            VALUES (?, 'catalogue', ?, ?, 1)
        ", [$lptId, $catalogueCode, $sortorder]);
        return true;
    }

    public static function addCourseToLearningPath(int $lptId, int $courseId, int $sortorder = 0): bool
    {
        return self::addMoodleCourseToLearningPath($lptId, $courseId, $sortorder);
    }

    public static function removeCourseFromLearningPath(int $lineId): bool
    {
        DB::delete('DELETE FROM mdl_local_learningpath_lines WHERE id = ?', [$lineId]);
        return true;
    }

    public static function updateLearningPathLineRequired(int $lineId, bool $required): bool
    {
        DB::update('UPDATE mdl_local_learningpath_lines SET required = ? WHERE id = ?', [(int)$required, $lineId]);
        return true;
    }

    public static function getLearningPathUsers(int $lptId): array
    {
        $enrolledCohorts = DB::select('SELECT cohort_id FROM mdl_local_learningpath_cohorts WHERE lpt_id = ?', [$lptId]);
        $cohortIds = array_map(fn($c) => $c->cohort_id, $enrolledCohorts);

        $users = DB::select("
            SELECT lpu.*, u.firstname, u.lastname, u.email
            FROM mdl_local_learningpath_users lpu
            JOIN mdl_user u ON lpu.u_id = u.id
            WHERE lpu.lpt_id = ?
            ORDER BY u.firstname ASC
        ", [$lptId]);

        foreach ($users as $user) {
            if (empty($user->assignee_id)) {
                $user->enrol_method = 'cohort';

                if (!empty($cohortIds)) {
                    $cohortMember = DB::selectOne("
                        SELECT cm.cohortid, c.name
                        FROM mdl_cohort_members cm
                        JOIN mdl_cohort c ON cm.cohortid = c.id
                        WHERE cm.userid = ? AND cm.cohortid IN (" . implode(',', $cohortIds) . ")
                    ", [$user->u_id]);

                    $user->cohort_name = $cohortMember->name ?? null;
                } else {
                    $user->cohort_name = null;
                }
            } else {
                $user->enrol_method = 'manual';
                $user->cohort_name = null;
            }
        }

        return $users;
    }

    public static function getLearningPathProgress(int $lptId): array
    {
        $users = self::getLearningPathUsers($lptId);
        $courses = self::getLearningPathCourses($lptId);
        $requiredCourses = array_values(array_filter($courses, static function($course) {
            return (int)($course->required ?? 0) === 1
                && ($course->course_type ?? null) === 'moodle'
                && !empty($course->course_id);
        }));

        $result = [];
        foreach ($users as $user) {
            $completedCount = 0;
            $totalCount = count($requiredCourses);

            foreach ($requiredCourses as $course) {
                $completion = DB::selectOne("
                    SELECT id FROM mdl_course_completions
                    WHERE userid = ? AND course = ? AND timecompleted IS NOT NULL AND timecompleted > 0
                ", [$user->u_id, $course->course_id]);

                if ($completion) {
                    $completedCount++;
                }
            }

            $progress = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

            $result[] = [
                'user' => $user,
                'completed_count' => $completedCount,
                'total_count' => $totalCount,
                'progress' => $progress,
            ];
        }

        return $result;
    }

    public static function getAllUsersForLearningPath(): array
    {
        return DB::select("
            SELECT id, username, firstname, lastname, email
            FROM mdl_user
            WHERE deleted = 0 AND suspended = 0
            ORDER BY firstname ASC
        ");
    }

    public static function getCohortsForLearningPath(): array
    {
        return DB::select("
            SELECT id, name
            FROM mdl_cohort
            WHERE visible = 1
            ORDER BY name ASC
        ");
    }

    public static function assignCohortToLearningPath(int $lptId, int $cohortId): bool
    {
        $existing = DB::selectOne("
            SELECT id FROM mdl_local_learningpath_cohorts
            WHERE lpt_id = ? AND cohort_id = ?
        ", [$lptId, $cohortId]);

        if (!$existing) {
            $userId = $_SESSION['USER']->id ?? 2;
            DB::insert("
                INSERT INTO mdl_local_learningpath_cohorts (lpt_id, cohort_id, assignee_id, timecreated)
                VALUES (?, ?, ?, ?)
            ", [$lptId, $cohortId, $userId, time()]);

            $members = DB::select('SELECT userid FROM mdl_cohort_members WHERE cohortid = ?', [$cohortId]);
            foreach ($members as $member) {
                self::assignUserToLearningPath($lptId, $member->userid, null);
            }
        }
        return true;
    }

    public static function getLearningPathEnrolledCohorts(int $lptId): array
    {
        return DB::select("
            SELECT lpc.*, c.name
            FROM mdl_local_learningpath_cohorts lpc
            JOIN mdl_cohort c ON lpc.cohort_id = c.id
            WHERE lpc.lpt_id = ?
            ORDER BY c.name ASC
        ", [$lptId]);
    }

    public static function unassignCohortFromLearningPath(int $lptId, int $cohortId): bool
    {
        DB::delete('DELETE FROM mdl_local_learningpath_cohorts WHERE lpt_id = ? AND cohort_id = ?', [$lptId, $cohortId]);

        $members = DB::select('SELECT userid FROM mdl_cohort_members WHERE cohortid = ?', [$cohortId]);
        foreach ($members as $member) {
            DB::delete('DELETE FROM mdl_local_learningpath_users WHERE lpt_id = ? AND u_id = ?', [$lptId, $member->userid]);
        }
        return true;
    }

    public static function assignUserToLearningPath(int $lptId, int $userId, int $assigneeId = null): bool
    {
        $existing = DB::selectOne("
            SELECT id FROM mdl_local_learningpath_users
            WHERE lpt_id = ? AND u_id = ?
        ", [$lptId, $userId]);

        if (!$existing) {
            if ($assigneeId !== null) {
                DB::insert("
                    INSERT INTO mdl_local_learningpath_users (lpt_id, u_id, assignee_id, timecreated)
                    VALUES (?, ?, ?, ?)
                ", [$lptId, $userId, $assigneeId, time()]);
            } else {
                DB::insert("
                    INSERT INTO mdl_local_learningpath_users (lpt_id, u_id, timecreated)
                    VALUES (?, ?, ?)
                ", [$lptId, $userId, time()]);
            }
        }
        return true;
    }

    public static function unassignUserFromLearningPath(int $lptId, int $userId): bool
    {
        DB::delete("
            DELETE FROM mdl_local_learningpath_users
            WHERE lpt_id = ? AND u_id = ?
        ", [$lptId, $userId]);
        return true;
    }
}
