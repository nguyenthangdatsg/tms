<?php

namespace App\Services\Moodle;

use Illuminate\Support\Facades\DB;

class CourseService
{
    public static function getCourses(string $visibility = 'online'): array
    {
        return DB::select("
            SELECT c.*, cfd.value as course_type
            FROM mdl_course c
            LEFT JOIN mdl_customfield_data cfd ON c.id = cfd.instanceid AND cfd.fieldid = 8
            WHERE c.id != 1
            ORDER BY c.fullname ASC
        ");
    }

    public static function getTmsCourses(string $type = null): array
    {
        $query = DB::connection('mysql_tms')->table('courses')
            ->where('status', 'active');

        if ($type) {
            $query->where('type', $type);
        }

        $courseIds = $query->pluck('moodle_course_id')->toArray();

        if (empty($courseIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));

        $courses = DB::select("
            SELECT c.*, cfd.value as course_type, cf_code.value as catalogue_code
            FROM mdl_course c
            LEFT JOIN mdl_customfield_data cfd ON c.id = cfd.instanceid AND cfd.fieldid = 8
            LEFT JOIN mdl_customfield_data cf_code ON c.id = cf_code.instanceid AND cf_code.fieldid = 4
            WHERE c.id != 1
            AND c.id IN ({$placeholders})
            ORDER BY c.fullname ASC
        ", $courseIds);

        foreach ($courses as $course) {
            $course->participants = self::getCourseParticipants($course->id);
            $course->completed = self::getCourseCompleted($course->id);
        }

        return $courses;
    }

    public static function getCourseParticipants(int $courseId): int
    {
        $enrolled = self::getEnrolledUsers($courseId);
        return count($enrolled);
    }

    public static function getCourseCompleted(int $courseId): int
    {
        return DB::selectOne("
            SELECT COUNT(DISTINCT userid) as count
            FROM mdl_course_completions
            WHERE course = ? AND timecompleted IS NOT NULL AND timecompleted > 0
        ", [$courseId])->count ?? 0;
    }

    public static function getCoursesByType(string $type): array
    {
        return DB::select("
            SELECT c.*, cfd.value as course_type
            FROM mdl_course c
            LEFT JOIN mdl_customfield_data cfd ON c.id = cfd.instanceid AND cfd.fieldid = 8
            WHERE c.id != 1
            AND cfd.value = ?
            ORDER BY c.fullname ASC
        ", [$type]);
    }

    public static function createCourse(array $data): int
    {
        $time = time();
        $fullname = $data['fullname'] ?? '';
        $shortname = $data['shortname'] ?? '';
        $summary = $data['summary'] ?? '';
        $startdate = !empty($data['startdate']) ? strtotime($data['startdate']) : 0;
        $enddate = !empty($data['enddate']) ? strtotime($data['enddate']) : 0;
        $visible = $data['visible'] ?? 1;

        DB::insert("
            INSERT INTO mdl_course
            (category, shortname, fullname, summary, summaryformat, visible, startdate, enddate, timecreated, timemodified)
            VALUES (1, ?, ?, ?, 1, ?, ?, ?, ?, ?)
        ", [
            $shortname,
            $fullname,
            $summary,
            $visible,
            $startdate,
            $enddate,
            $time,
            $time
        ]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        $courseId = $result[0]->id;

        if (getenv('MOODLE_WRITE_ENABLED') === 'true') {
            DB::connection('mysql_tms')->table('courses')->insert([
                'moodle_course_id' => $courseId,
                'type' => $data['type'] ?? 'online',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $courseId;
    }

    public static function deleteCourse(int $id): bool
    {
        DB::update("
            UPDATE mdl_course SET visible = 0, timemodified = ? WHERE id = ?
        ", [time(), $id]);

        if (getenv('MOODLE_WRITE_ENABLED') === 'true') {
            DB::connection('mysql_tms')->table('courses')
                ->where('moodle_course_id', $id)
                ->update(['status' => 'deleted', 'updated_at' => now()]);
        }

        return true;
    }

    public static function updateCourse(int $id, array $data): bool
    {
        $allowedFields = ['fullname', 'shortname', 'summary', 'startdate', 'enddate', 'visible'];
        $sets = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedFields)) {
                continue;
            }
            $sets[] = "$key = ?";
            if (in_array($key, ['startdate', 'enddate']) && !empty($value)) {
                $values[] = strtotime($value);
            } else {
                $values[] = $value;
            }
        }
        if (empty($sets)) {
            return false;
        }
        $values[] = time();
        $values[] = $id;

        DB::update("
            UPDATE mdl_course
            SET " . implode(', ', $sets) . ", timemodified = ?
            WHERE id = ?
        ", $values);

        return true;
    }

    public static function getCourseImage(int $courseId): ?string
    {
        $result = DB::selectOne("
            SELECT f.filename
            FROM mdl_files f
            JOIN mdl_context ctx ON f.contextid = ctx.id
            WHERE ctx.contextlevel = 70
            AND ctx.instanceid = ?
            AND f.component = 'core'
            AND f.filearea = 'overviewfiles'
            ORDER BY f.id DESC
            LIMIT 1
        ", [$courseId]);

        return $result->filename ?? null;
    }

    public static function updateCourseCustomField(int $courseId, string $value, int $fieldId = 8): bool
    {
        $existing = DB::selectOne("
            SELECT id FROM mdl_customfield_data
            WHERE fieldid = ? AND instanceid = ?
        ", [$fieldId, $courseId]);

        if ($existing) {
            DB::update("
                UPDATE mdl_customfield_data
                SET value = ?, timemodified = ?
                WHERE fieldid = ? AND instanceid = ?
            ", [$value, time(), $fieldId, $courseId]);
        } else {
            DB::insert("
                INSERT INTO mdl_customfield_data (fieldid, instanceid, value, valueformat, timecreated, timemodified)
                VALUES (?, ?, ?, 0, ?, ?)
            ", [$fieldId, $courseId, $value, time(), time()]);
        }

        return true;
    }

    public static function getCourse(int $id): ?object
    {
        $results = DB::select("
            SELECT c.*,
                (SELECT f.filename
                 FROM mdl_files f
                 JOIN mdl_context ctx ON f.contextid = ctx.id
                 WHERE ctx.contextlevel = 70 AND ctx.instanceid = c.id
                 AND f.component = 'core' AND f.filearea = 'overviewfiles'
                 ORDER BY f.id DESC LIMIT 1) as course_image
            FROM mdl_course c
            WHERE c.id = ?
        ", [$id]);
        return $results[0] ?? null;
    }

    public static function getEnrolledUsers(int $courseid): array
    {
        return DB::select("
            SELECT u.*, e.enrol as enrol_method, c.name as cohort_name, ue.timestart, ue.timeend,
                cc.timecompleted as completed
            FROM mdl_user u
            JOIN mdl_enrol e ON e.courseid = ?
            JOIN mdl_user_enrolments ue ON ue.enrolid = e.id AND ue.userid = u.id
            LEFT JOIN mdl_cohort c ON e.customint1 = c.id AND e.enrol = 'cohort'
            LEFT JOIN mdl_course_completions cc ON cc.userid = u.id AND cc.course = ?
            WHERE u.deleted = 0
            ORDER BY u.firstname
        ", [$courseid, $courseid]);
    }

    public static function enrolUser(int $userid, int $courseid, int $roleid = 5): bool
    {
        $enrol = DB::selectOne("
            SELECT * FROM mdl_enrol
            WHERE courseid = ? AND enrol = 'manual'
        ", [$courseid]);

        if (!$enrol) {
            if (getenv('MOODLE_WRITE_ENABLED') === 'true') {
                DB::insert("
                    INSERT INTO mdl_enrol (enrol, status, courseid, roleid, sortorder, timecreated, timemodified)
                    VALUES ('manual', 0, ?, 5, 0, ?, ?)
                ", [$courseid, time(), time()]);

                $enrol = DB::selectOne("
                    SELECT * FROM mdl_enrol
                    WHERE courseid = ? AND enrol = 'manual'
                ", [$courseid]);
            } else {
                return false;
            }
        }

        if (!$enrol) {
            return false;
        }

        $existing = DB::selectOne("
            SELECT * FROM mdl_user_enrolments
            WHERE userid = ? AND enrolid = ?
        ", [$userid, $enrol->id]);

        if ($existing) {
            return true;
        }

        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }

        DB::insert("
            INSERT INTO mdl_user_enrolments (userid, enrolid, status, timestart, timeend, timecreated, timemodified)
            VALUES (?, ?, 0, ?, 0, ?, ?)
        ", [
            $userid,
            $enrol->id,
            time(),
            time(),
            time()
        ]);
        return true;
    }

    public static function enrolUsersToCourse(array $userIds, int $courseid, int $roleid = 5, string $enrolMethod = 'manual'): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }
        foreach ($userIds as $uid) {
            if ($uid) {
                if ($enrolMethod === 'cohort') {
                    self::enrolUserToCohort((int)$uid, $courseid);
                } else {
                    self::enrolUser((int)$uid, $courseid, $roleid);
                }
            }
        }
        return true;
    }

    private static function enrolUserToCohort(int $userid, int $courseid): bool
    {
        $enrol = DB::selectOne("
            SELECT * FROM mdl_enrol
            WHERE courseid = ? AND enrol = 'cohort'
        ", [$courseid]);

        if (!$enrol) {
            return false;
        }

        $existing = DB::selectOne("
            SELECT * FROM mdl_user_enrolments
            WHERE userid = ? AND enrolid = ?
        ", [$userid, $enrol->id]);

        if ($existing) {
            return true;
        }

        DB::insert("
            INSERT INTO mdl_user_enrolments (userid, enrolid, status, timestart, timeend, timecreated, timemodified)
            VALUES (?, ?, 0, ?, 0, ?, ?)
        ", [
            $userid,
            $enrol->id,
            time(),
            time(),
            time()
        ]);
        return true;
    }

    public static function getGroups(int $courseid): array
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return [];
        }
        return DB::select(
            'SELECT g.id, g.name FROM mdl_groups g JOIN mdl_course_groups cg ON cg.groupid = g.id WHERE cg.courseid = ?',
            [$courseid]
        );
    }

    public static function getGroupMembers(int $groupid): array
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return [];
        }
        return DB::select('SELECT userid FROM mdl_groups_members WHERE groupid = ?', [$groupid]);
    }

    public static function enrolUsersToGroup(array $groupIds, int $courseid, int $roleid = 5): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }
        $uids = [];
        foreach ($groupIds as $gid) {
            foreach (self::getGroupMembers((int)$gid) as $row) {
                $uids[] = $row->userid ?? 0;
            }
        }
        $uids = array_values(array_unique(array_filter($uids)));
        if (empty($uids)) {
            return true;
        }
        return self::enrolUsersToCourse($uids, $courseid, $roleid);
    }

    public static function getCohortsForCourse(int $courseid): array
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return [];
        }
        return DB::select(
            'SELECT c.id, c.name FROM mdl_cohort c JOIN mdl_cohort_courses cc ON cc cohortid = c.id WHERE cc courseid = ?',
            [$courseid]
        );
    }

    public static function getCohorts(): array
    {
        return DB::select('SELECT id, name FROM mdl_cohort WHERE id > 1 ORDER BY name ASC');
    }

    public static function getEnrolledCohorts(int $courseid): array
    {
        return DB::select("
            SELECT c.id, c.name, e.customint1 as cohortid
            FROM mdl_enrol e
            JOIN mdl_cohort c ON e.customint1 = c.id
            WHERE e.courseid = ? AND e.enrol = 'cohort'
            ORDER BY c.name
        ", [$courseid]);
    }

    public static function getCohortMembers(int $cohortid): array
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return [];
        }
        return DB::select('SELECT userid FROM mdl_cohort_members WHERE cohortid = ?', [$cohortid]);
    }

    public static function enrolUsersToCohorts(array $cohortIds, int $courseid, int $roleid = 5): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }
        $uids = [];
        foreach ($cohortIds as $cid) {
            foreach (self::getCohortMembers((int)$cid) as $row) {
                $uids[] = $row->userid ?? 0;
            }
        }
        $uids = array_values(array_unique(array_filter($uids)));
        if (empty($uids)) {
            return true;
        }

        if (getenv('MOODLE_WRITE_ENABLED') === 'true') {
            self::createCohortEnrolMethod($courseid, $cohortIds[0]);
        }

        return self::enrolUsersToCourse($uids, $courseid, $roleid, 'cohort');
    }

    private static function createCohortEnrolMethod(int $courseid, int $cohortid): bool
    {
        $existing = DB::selectOne("
            SELECT * FROM mdl_enrol
            WHERE courseid = ? AND enrol = 'cohort' AND customint1 = ?
        ", [$courseid, $cohortid]);

        if ($existing) {
            return true;
        }

        DB::insert("
            INSERT INTO mdl_enrol (enrol, status, courseid, roleid, customint1, timecreated, timemodified)
            VALUES ('cohort', 0, ?, 5, ?, ?, ?)
        ", [$courseid, $cohortid, time(), time()]);

        return true;
    }

    public static function unenrolCohort(int $cohortid, int $courseid): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }

        $enrol = DB::selectOne("
            SELECT * FROM mdl_enrol
            WHERE courseid = ? AND enrol = 'cohort' AND customint1 = ?
        ", [$courseid, $cohortid]);

        if (!$enrol) {
            return false;
        }

        DB::delete('DELETE FROM mdl_user_enrolments WHERE enrolid = ?', [$enrol->id]);
        DB::delete('DELETE FROM mdl_enrol WHERE id = ?', [$enrol->id]);

        return true;
    }

    public static function uploadCourseImage(int $courseId, $file): bool
    {
        $content = file_get_contents($file->getRealPath());
        $contenthash = sha1($content);
        $filesize = strlen($content);
        $mimetype = $file->getMimeType();
        $filename = $file->getClientOriginalName();

        $course = DB::selectOne('SELECT id FROM mdl_course WHERE id = ?', [$courseId]);
        if (!$course) {
            return false;
        }

        $context = DB::selectOne("
            SELECT id FROM mdl_context
            WHERE contextlevel = 70 AND instanceid = ?
        ", [$courseId]);

        if (!$context) {
            $contextId = self::createCourseContext($courseId);
        } else {
            $contextId = $context->id;
        }

        DB::delete("
            DELETE FROM mdl_files
            WHERE contextid = ? AND component = 'core' AND filearea = 'overviewfiles'
        ", [$contextId]);

        $filepath = '/var/www/moodledata/files/' . substr($contenthash, 0, 2) . '/' . substr($contenthash, 2, 2);
        if (!is_dir($filepath)) {
            mkdir($filepath, 0777, true);
        }
        file_put_contents($filepath . '/' . $contenthash, $content);

        DB::insert("
            INSERT INTO mdl_files
            (contenthash, pathnamehash, contextid, component, filearea, itemid, filepath, filename, filesize, mimetype, timecreated, timemodified, sortorder)
            VALUES (?, ?, ?, 'core', 'overviewfiles', 0, '/', ?, ?, ?, ?, ?, 0)
        ", [
            $contenthash,
            sha1($filepath . '/' . $filename),
            $contextId,
            $filename,
            $filesize,
            $mimetype,
            time(),
            time()
        ]);

        DB::update("
            UPDATE mdl_course SET timemodified = ? WHERE id = ?
        ", [time(), $courseId]);

        return true;
    }

    private static function createCourseContext(int $courseId): int
    {
        $maxId = DB::selectOne('SELECT MAX(id) as maxid FROM mdl_context');
        $newId = ($maxId->maxid ?? 0) + 1;

        DB::insert("
            INSERT INTO mdl_context (id, contextlevel, instanceid, depth, path, locked)
            VALUES (?, 70, ?, 2, NULL, 0)
        ", [$newId, $courseId]);

        DB::update("
            UPDATE mdl_context SET path = CONCAT('/', ?, '/', ?) WHERE id = ?
        ", [$newId, $newId, $newId]);

        return $newId;
    }

    public static function unenrolUser(int $userid, int $courseid): bool
    {
        $enrol = DB::selectOne("
            SELECT * FROM mdl_enrol
            WHERE courseid = ? AND enrol = 'manual'
        ", [$courseid]);

        if (!$enrol) {
            return false;
        }

        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }

        DB::delete("
            DELETE FROM mdl_user_enrolments
            WHERE userid = ? AND enrolid = ?
        ", [$userid, $enrol->id]);

        return true;
    }

    public static function getCategories(): array
    {
        return DB::select("
            SELECT * FROM mdl_course_categories
            ORDER BY sortorder ASC
        ");
    }

    public static function updateCategory(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $sets = [];
        $values = [];
        foreach ($data as $k => $v) {
            $sets[] = "$k = ?";
            $values[] = $v;
        }
        $values[] = time();
        $values[] = $id;
        DB::update('UPDATE mdl_course_categories SET ' . implode(', ', $sets) . ', timemodified = ? WHERE id = ?', $values);
        return true;
    }

    public static function deleteCategory(int $id): bool
    {
        DB::update('UPDATE mdl_course_categories SET visible = 0, timemodified = ? WHERE id = ?', [time(), $id]);
        return true;
    }
}
