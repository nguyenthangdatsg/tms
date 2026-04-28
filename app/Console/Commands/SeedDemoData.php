<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedDemoData extends Command
{
    protected $signature = 'demo:seed {--fresh : Xóa toàn bộ dữ liệu demo trước khi seed}';
    protected $description = 'Seed demo data for TMS';

    private array $createdUserIds = [];
    private array $createdCourseIds = [];
    private array $createdQuizIds = [];
    private array $createdQuestionIds = [];
    private array $createdSetIds = [];
    private array $seededCohortIds = [];

    public function handle(): int
    {
        $this->info('=== TMS Demo Data Seeder ===');

        if ($this->option('fresh')) {
            $this->warn('Xóa dữ liệu demo cũ...');
            $this->cleanDemoData();
        }

        $this->info('Tạo demo data...');
        $this->line('');

        // Seed steps - let try/catch handle missing tables gracefully
        try { $this->seedCategories(); } catch (\Throwable $e) { $this->warn('1. Categories: '.$e->getMessage()); }
        try { $this->seedCatalogueCourses(); } catch (\Throwable $e) { $this->warn('2. Catalogue: '.$e->getMessage()); }
        try { $this->seedUsers(); } catch (\Throwable $e) { $this->warn('3. Users: '.$e->getMessage()); }
        try { $this->seedQuestions(); } catch (\Throwable $e) { $this->warn('4. Questions: '.$e->getMessage()); }
        try { $this->seedQuestionSets(); } catch (\Throwable $e) { $this->warn('5. Question Sets: '.$e->getMessage()); }
        try { $this->seedCourses(); } catch (\Throwable $e) { $this->warn('6. Courses: '.$e->getMessage()); }
        try { $this->seedQuizzes(); } catch (\Throwable $e) { $this->warn('7. Quizzes: '.$e->getMessage()); }
        try { $this->seedEnrollments(); } catch (\Throwable $e) { $this->warn('8. Enrollments: '.$e->getMessage()); }
        try { $this->seedCohorts(); } catch (\Throwable $e) { $this->warn('9. Cohorts: '.$e->getMessage()); }
        try { $this->seedMoodleActivities(); } catch (\Throwable $e) { $this->warn('10. Activities: '.$e->getMessage()); }
        try { $this->seedPermissions(); } catch (\Throwable $e) { $this->warn('11. Permissions: '.$e->getMessage()); }
        try { $this->seedOrganizationUnits(); } catch (\Throwable $e) { $this->warn('12. Organization: '.$e->getMessage()); }

        $this->newLine();
        $this->info('=== Demo Data Created ===');
        $this->table(['Type', 'Count'], [
            ['Users', count($this->createdUserIds)],
            ['Moodle Courses', count($this->createdCourseIds)],
            ['Questions', count($this->createdQuestionIds)],
            ['Question Sets', count($this->createdSetIds)],
            ['Quizzes', count($this->createdQuizIds)],
        ]);

        return Command::SUCCESS;
    }

private function seedCategories(): void
    {
        $this->info('1. Tạo Categories (Catalogue)...');

        $categories = [
            ['name' => 'Công nghệ thông tin', 'parent' => 0],
            ['name' => 'Kỹ năng mềm', 'parent' => 0],
            ['name' => 'An toàn lao động', 'parent' => 0],
        ];

        // Try mdl_local_catalogue_categories first
        foreach ($categories as $cat) {
            try {
                $exists = DB::connection('mysql')->table('mdl_local_catalogue_categories')
                    ->where('name', $cat['name'])->exists();
                if (!$exists) {
                    DB::connection('mysql')->table('mdl_local_catalogue_categories')->insert([
                        'name' => $cat['name'],
                        'parent' => $cat['parent'],
                        'sortorder' => 0,
                        'visible' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // Table might not exist, skip
            }
        }
        $this->info('   ✓ Done');
    }

    private function seedCatalogueCourses(): void
    {
        $this->info('2. Tạo Catalogue Courses...');

        $courses = [
            ['name' => 'Lập trình PHP', 'code' => 'PHP101', 'type' => 'programming'],
            ['name' => 'Lập trình Python', 'code' => 'PY101', 'type' => 'programming'],
            ['name' => 'Kỹ năng giao tiếp', 'code' => 'SK101', 'type' => 'softskill'],
            ['name' => 'An toàn PCCC', 'code' => 'SA101', 'type' => 'safety'],
            ['name' => 'DevOps Basics', 'code' => 'DO101', 'type' => 'devops'],
            ['name' => 'UI/UX Essentials', 'code' => 'UX101', 'type' => 'design'],
        ];

        foreach ($courses as $course) {
            try {
                $exists = DB::connection('mysql')->table('mdl_local_catalogue_courses')
                    ->where('code', $course['code'])->exists();
                if (!$exists) {
                    DB::connection('mysql')->table('mdl_local_catalogue_courses')->insert([
                        'category_id' => 1,
                        'name' => $course['name'],
                        'code' => $course['code'],
                        'description' => 'Khóa học ' . $course['name'],
                        'duration' => '8 giờ',
                        'type' => $course['type'],
                        'visible' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // Skip if table doesn't exist
            }
        }
        $this->info('   ✓ Done');
    }

    private function seedUsers(): void
    {
        $this->info('3. Tạo Users...');

        $users = [
            ['username' => 'user_demo_1', 'firstname' => 'Nguyễn', 'lastname' => 'Văn A', 'email' => 'user_demo_1@test.com'],
            ['username' => 'user_demo_2', 'firstname' => 'Trần', 'lastname' => 'Văn B', 'email' => 'user_demo_2@test.com'],
            ['username' => 'user_demo_3', 'firstname' => 'Lê', 'lastname' => 'Thị C', 'email' => 'user_demo_3@test.com'],
            ['username' => 'user_demo_4', 'firstname' => 'Phạm', 'lastname' => 'Văn D', 'email' => 'user_demo_4@test.com'],
            ['username' => 'user_demo_5', 'firstname' => 'Hoàng', 'lastname' => 'Văn E', 'email' => 'user_demo_5@test.com'],
        ];

        foreach ($users as $user) {
            try {
                $exists = DB::connection('mysql')->selectOne(
                    "SELECT id FROM mdl_user WHERE username = ?", [$user['username']]
                );
                if (!$exists) {
                    $id = DB::connection('mysql')->table('mdl_user')->insertGetId([
                        'username' => $user['username'],
                        'firstname' => $user['firstname'],
                        'lastname' => $user['lastname'],
                        'email' => $user['email'],
                        'mnethostid' => 1,
                        'auth' => 'manual',
                        'confirmed' => 1,
                        'timemodified' => time(),
                    ]);
                    $this->createdUserIds[] = $id;
                }
            } catch (\Throwable $e) {
                // Skip
            }
        }
        $this->info('   ✓ Created ' . count($this->createdUserIds) . ' users');
    }

        // Use mdl_local_catalogue_categories in mysql connection
        $categories = [
            ['name' => 'Công nghệ thông tin', 'parent' => 0],
            ['name' => 'Kỹ năng mềm', 'parent' => 0],
            ['name' => 'An toàn lao động', 'parent' => 0],
        ];

        $table = \Schema::connection('mysql')->hasTable('mdl_local_catalogue_categories') 
            ? 'mdl_local_catalogue_categories' 
            : 'categories';
        $conn = \Schema::connection('mysql')->hasTable('mdl_local_catalogue_categories') 
            ? 'mysql' 
            : 'mysql_tms';

        foreach ($categories as $cat) {
            $exists = DB::connection($conn)->table($table)
                ->where('name', $cat['name'])->exists();

            if (!$exists) {
                DB::connection($conn)->table($table)->insert([
                    'name' => $cat['name'],
                    'parent' => $cat['parent'],
                    'sortorder' => 0,
                    'visible' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
$this->info('   ✓ Created ' . count($categories) . ' categories');
    }

    private function seedCohorts(): void
    {
        // Seed cohorts and map to first 2 courses
        $this->info('9. Tạo Cohorts...');
        if (!\Schema::connection('mysql')->hasTable('mdl_cohort')) {
            $this->warn('mdl_cohort not found; skipping cohorts seed');
            return;
        }

        $cohorts = [
            ['name' => 'Demo Cohort A', 'description' => 'Demo cohort A'],
            ['name' => 'Demo Cohort B', 'description' => 'Demo cohort B'],
        ];

        foreach ($cohorts as $c) {
            $exists = DB::connection('mysql')->table('mdl_cohort')->where('name', $c['name'])->exists();
            if (!$exists) {
                DB::connection('mysql')->table('mdl_cohort')->insert([
                    'name' => $c['name'],
                    'description' => $c['description'],
                    'timecreated' => time(),
                    'timemodified' => time(),
                ]);
            }
        }

        // collect cohort ids
        $this->seededCohortIds = DB::connection('mysql')->table('mdl_cohort')
            ->whereIn('name', ['Demo Cohort A', 'Demo Cohort B'])
            ->pluck('id')
            ->toArray();

        // Link cohorts to first two courses if available
        $courseIds = array_slice($this->createdCourseIds, 0, 2);
        foreach ($this->seededCohortIds as $cid) {
            foreach ($courseIds as $courseId) {
                $exists = DB::connection('mysql')->table('mdl_cohort_courses')
                    ->where(['cohortid' => $cid, 'courseid' => $courseId])
                    ->exists();
                if (!$exists) {
                    DB::connection('mysql')->table('mdl_cohort_courses')->insert([
                        'cohortid' => $cid,
                        'courseid' => $courseId,
                        'timecreated' => time(),
                        'timemodified' => time(),
                    ]);
                }
            }
        }

        // Seed cohort members with first two users for each cohort if available
        $userIds = array_slice($this->createdUserIds, 0, 2);
        foreach ($this->seededCohortIds as $cid) {
            foreach ($userIds as $uid) {
                $exists = DB::connection('mysql')->table('mdl_cohort_members')
                    ->where(['cohortid' => $cid, 'userid' => $uid])
                    ->exists();
                if (!$exists) {
                    DB::connection('mysql')->table('mdl_cohort_members')->insert([
                        'cohortid' => $cid,
                        'userid' => $uid,
                        'timecreated' => time(),
                        'timemodified' => time(),
                    ]);
                }
            }
        }

        $this->info('   ✓ Seeded cohorts: ' . count($this->seededCohortIds));
    }

    private function seedMoodleActivities(): void
    {
        // Link quizzes to Moodle course modules as activities
        $this->info('10. Seed Moodle Activities (Quiz modules)...');
        if (!\Schema::connection('mysql')->hasTable('mdl_course_modules')) {
            $this->warn('mdl_course_modules not found; skipping activities seed');
            return;
        }
        $quizModuleId = \DB::connection('mysql')->table('mdl_modules')->where('name', 'quiz')->value('id');
        if (!$quizModuleId) {
            $this->warn('Quiz module id not found; skipping activities seed');
            return;
        }
        foreach ($this->createdQuizIds as $idx => $quizId) {
            $courseId = $this->createdCourseIds[$idx % count($this->createdCourseIds)];
            $exists = \DB::connection('mysql')->table('mdl_course_modules')
                ->where(['course'=>$courseId, 'module'=>$quizModuleId, 'instance'=>$quizId])
                ->exists();
            if (!$exists) {
                \DB::connection('mysql')->table('mdl_course_modules')->insert([
                    'course' => $courseId,
                    'module' => $quizModuleId,
                    'instance' => $quizId,
                    'section' => 0,
                    'added' => time(),
                ]);
            }
        }
        $this->info('   ✓ Moodle quiz activities seeded');
    }

    private function seedCatalogueCourses(): void
    {
        $this->info('2. Tạo Catalogue Courses...');

        // Check both possible table names and locations
        $hasCategories = \Schema::connection('mysql')->hasTable('mdl_local_catalogue_categories') 
            || \Schema::connection('mysql_tms')->hasTable('categories');
        $hasCourses = \Schema::connection('mysql')->hasTable('mdl_local_catalogue_courses')
            || \Schema::connection('mysql_tms')->hasTable('courses');
            
        if (!$hasCategories || !$hasCourses) {
            $this->warn('Skipping catalogue courses - tables not found');
            return;
        }

        $courses = [
            ['name' => 'Lập trình PHP', 'code' => 'PHP101', 'type' => 'programming'],
            ['name' => 'Lập trình Python', 'code' => 'PY101', 'type' => 'programming'],
            ['name' => 'Kỹ năng giao tiếp', 'code' => 'SK101', 'type' => 'softskill'],
            ['name' => 'An toàn PCCC', 'code' => 'SA101', 'type' => 'safety'],
            ['name' => 'DevOps Basics', 'code' => 'DO101', 'type' => 'devops'],
            ['name' => 'UI/UX Essentials', 'code' => 'UX101', 'type' => 'design'],
        ];

        // Use mdl_local_catalogue_* or tms_* depending on what's available
        $catTable = \Schema::connection('mysql')->hasTable('mdl_local_catalogue_categories') 
            ? 'mdl_local_catalogue_categories' 
            : 'categories';
        $courseTable = \Schema::connection('mysql')->hasTable('mdl_local_catalogue_courses')
            ? 'mdl_local_catalogue_courses'
            : 'courses';
        $conn = \Schema::connection('mysql')->hasTable('mdl_local_catalogue_categories')
            ? 'mysql'
            : 'mysql_tms';

        $catId = DB::connection($conn)->table($catTable)
            ->where('name', 'Công nghệ thông tin')->value('id') ?? 1;

        foreach ($courses as $course) {
            $exists = DB::connection($conn)->table($courseTable)
                ->where('code', $course['code'])->exists();

            if (!$exists) {
                DB::connection($conn)->table($courseTable)->insert([
                    'category_id' => $catId,
                    'name' => $course['name'],
                    'code' => $course['code'],
                    'description' => 'Khóa học ' . $course['name'],
                    'duration' => '8 giờ',
                    'type' => $course['type'],
                    'visible' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->info('   ✓ Created ' . count($courses) . ' catalogue courses');
    }

    private function seedUsers(): void
    {
        $this->info('3. Tạo Users...');
        if (!\Schema::connection('mysql')->hasTable('mdl_user')) {
            throw new \RuntimeException('Missing table mdl_user. Ensure Moodle schema is loaded.');
        }
        $users = [
            ['username' => 'user_demo_1', 'firstname' => 'Nguyễn', 'lastname' => 'Văn A', 'email' => 'user_demo_1@test.com'],
            ['username' => 'user_demo_2', 'firstname' => 'Trần', 'lastname' => 'Văn B', 'email' => 'user_demo_2@test.com'],
            ['username' => 'user_demo_3', 'firstname' => 'Lê', 'lastname' => 'Thị C', 'email' => 'user_demo_3@test.com'],
            ['username' => 'user_demo_4', 'firstname' => 'Phạm', 'lastname' => 'Văn D', 'email' => 'user_demo_4@test.com'],
            ['username' => 'user_demo_5', 'firstname' => 'Hoàng', 'lastname' => 'Văn E', 'email' => 'user_demo_5@test.com'],
        ];

        foreach ($users as $user) {
            $exists = DB::selectOne("SELECT id FROM mdl_user WHERE username = ?", [$user['username']]);
            if (!$exists) {
                $id = $this->createMoodleUser($user);
                $this->createdUserIds[] = $id;
            } else {
                $this->createdUserIds[] = $exists->id;
            }
        }
        $this->info('   ✓ Created ' . count($this->createdUserIds) . ' users');
    }

    private function createMoodleUser(array $data): int
    {
        $time = time();
        $passwordHash = $this->hashPassword('password123');

        DB::insert("
            INSERT INTO mdl_user 
            (username, password, firstname, lastname, email, auth, confirmed, deleted, suspended, mnethostid, policyagreed, timecreated, timemodified) 
            VALUES (?, ?, ?, ?, ?, 'manual', 1, 0, 0, 1, 0, ?, ?)
        ", [
            $data['username'],
            $passwordHash,
            $data['firstname'],
            $data['lastname'],
            $data['email'],
            $time,
            $time
        ]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        return (int)$result[0]->id;
    }

    private function hashPassword(string $password): string
    {
        $rounds = 10000;
        $randombytes = random_bytes(16);
        $salt = substr(strtr(base64_encode($randombytes), '+', '.'), 0, 16);
        return crypt($password, implode('$', ['', '6', "rounds={$rounds}", $salt, '']));
    }

    private function seedQuestions(): void
    {
        // Guard against missing Moodle question schema
        if (!Schema::connection('mysql')->hasTable('mdl_question')) {
            $this->warn('mdl_question table not found; skipping seedQuestions');
            return;
        }
        // If expected column is missing, skip as well
        if (!Schema::connection('mysql')->hasColumn('mdl_question', 'category')) {
            $this->warn('mdl_question.category column missing; skipping seedQuestions');
            return;
        }
        $this->info('4. Tạo Questions...');

        $questions = [
            ['name' => 'Giải thích biến trong PHP', 'qtype' => 'essay', 'questiontext' => 'Trình bày khái niệm về biến và cách khai báo biến trong PHP?'],
            ['name' => 'Array trong PHP', 'qtype' => 'multichoice', 'questiontext' => 'Câu lệnh nào tạo array trong PHP?'],
            ['name' => 'Vòng lặp for', 'qtype' => 'truefalse', 'questiontext' => 'Vòng lặp for có thể lặp vô hạn không?'],
            ['name' => 'Hàm trong Python', 'qtype' => 'essay', 'questiontext' => 'Trình bày cách định nghĩa và gọi hàm trong Python?'],
            ['name' => 'List comprehension', 'qtype' => 'multichoice', 'questiontext' => 'Cú pháp nào là List comprehension hợp lệ trong Python?'],
            ['name' => 'Giao tiếp hiệu quả', 'qtype' => 'essay', 'questiontext' => 'Nêu 5 nguyên tắc giao tiếp hiệu quả?'],
            ['name' => 'Nguyên tắc PCCC', 'qtype' => 'multichoice', 'questiontext' => 'Khi xảy ra cháy, bước đầu tiên cần làm gì?'],
            ['name' => 'Quy trình thoát hiểm', 'qtype' => 'truefalse', 'questiontext' => 'Khi thoát hiểm nên chạy bộ qua cầu thang bộ?'],
            ['name' => 'Dữ liệu và thuật toán', 'qtype' => 'shortanswer', 'questiontext' => 'Đưa ra ví dụ thuật toán sắp xếp.'],
            ['name' => 'Kiến thức mạng', 'qtype' => 'multichoice', 'questiontext' => 'Địa chỉ IP 192.168.0.1 là loại gì?'],
        ];

        foreach ($questions as $q) {
            $this->createdQuestionIds[] = $this->createQuestion($q);
        }
        $this->info('   ✓ Created ' . count($this->createdQuestionIds) . ' questions');
    }

    private function createQuestion(array $data): int
    {
        $time = time();
        $qtypeId = $this->getQuestionTypeId($data['qtype']);

        DB::insert("
            INSERT INTO mdl_question 
            (category, qtype, name, questiontext, questiontextformat, defaultmark, timecreated, timemodified)
            VALUES (1, ?, ?, ?, 1, 1, ?, ?)
        ", [$qtypeId, $data['name'], $data['questiontext'], $time, $time]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        return (int)$result[0]->id;
    }

    private function getQuestionTypeId(string $qtype): string
    {
        $map = [
            'essay' => 'essay',
            'multichoice' => 'multichoice',
            'truefalse' => 'truefalse',
            'shortanswer' => 'shortanswer',
        ];
        return $map[$qtype] ?? 'essay';
    }

    private function seedQuestionSets(): void
    {
        $this->info('5. Tạo Question Sets...');

        $sets = [
            ['name' => 'Bộ đề PHP cơ bản', 'description' => 'Các câu hỏi cơ bản về PHP', 'question_ids' => [1, 2, 3]],
            ['name' => 'Bộ đề Python nâng cao', 'description' => 'Các câu hỏi nâng cao về Python', 'question_ids' => [4, 5]],
            ['name' => 'Bộ đề Kỹ năng mềm', 'description' => 'Các câu hỏi về kỹ năng giao tiếp', 'question_ids' => [6]],
            ['name' => 'Bộ đề An toàn', 'description' => 'Các câu hỏi về PCCC', 'question_ids' => [7, 8]],
            ['name' => 'Bộ đề Dữ liệu & Mạng', 'description' => 'Câu hỏi về dữ liệu và mạng', 'question_ids' => [9, 10]],
        ];

        foreach ($sets as $set) {
            $setId = $this->createQuestionSet($set);
            $this->createdSetIds[] = $setId;
        }
        $this->info('   ✓ Created ' . count($this->createdSetIds) . ' question sets');
    }

    private function createQuestionSet(array $data): int
    {
        $time = time();
        DB::insert("
            INSERT INTO mdl_question_set (name, description, timecreated, timemodified)
            VALUES (?, ?, ?, ?)
        ", [$data['name'], $data['description'], $time, $time]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        $setId = (int)$result[0]->id;

        $position = 1;
        foreach ($data['question_ids'] as $qId) {
            DB::insert("
                INSERT INTO mdl_question_set_questions (set_id, question_id, position)
                VALUES (?, ?, ?)
            ", [$setId, $qId, $position++]);
        }

        return $setId;
    }

    private function seedCourses(): void
    {
        $this->info('6. Tạo Moodle Courses...');

        $courses = [
            ['fullname' => 'Lập trình PHP cho người mới', 'shortname' => 'PHP101', 'type' => 'Online'],
            ['fullname' => 'Lập trình Python nâng cao', 'shortname' => 'PY201', 'type' => 'Offline'],
            ['fullname' => 'Kỹ năng giao tiếp doanh nghiệp', 'shortname' => 'SK101', 'type' => 'Blended'],
            ['fullname' => 'An toàn PCCC cơ bản', 'shortname' => 'SA101', 'type' => 'Online'],
        ];

        foreach ($courses as $course) {
            $courseId = $this->createMoodleCourse($course);
            $this->createdCourseIds[] = $courseId;

            // Register in TMS
            $typeMap = ['Online' => 'online', 'Offline' => 'offline', 'Blended' => 'blended'];
            DB::connection('mysql_tms')->table('courses')->insert([
                'moodle_course_id' => $courseId,
                'type' => $typeMap[$course['type']],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->info('   ✓ Created ' . count($this->createdCourseIds) . ' courses');
    }

    private function createMoodleCourse(array $data): int
    {
        $time = time();
        $typeFieldId = 8;

        DB::insert("
            INSERT INTO mdl_course 
            (category, shortname, fullname, summary, summaryformat, visible, startdate, enddate, timecreated, timemodified)
            VALUES (1, ?, ?, '', 1, 1, 0, 0, ?, ?)
        ", [
            $data['shortname'],
            $data['fullname'],
            $time,
            $time
        ]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        $courseId = (int)$result[0]->id;

        DB::insert("
            INSERT INTO mdl_customfield_data (fieldid, instanceid, value, valueformat, timecreated, timemodified)
            VALUES (?, ?, ?, 0, ?, ?)
        ", [$typeFieldId, $courseId, $data['type'], $time, $time]);

        return $courseId;
    }

    private function seedQuizzes(): void
    {
        $this->info('7. Tạo Quizzes...');

        if (empty($this->createdCourseIds)) {
            $this->warn('   ⚠ No courses found, skipping quizzes');
            return;
        }

        $quizzes = [
            ['name' => 'Kiểm tra PHP cơ bản', 'course_idx' => 0, 'timelimit' => 1800],
            ['name' => 'Kiểm tra Python nâng cao', 'course_idx' => 1, 'timelimit' => 3600],
            ['name' => 'Kiểm tra Kỹ năng mềm', 'course_idx' => 2, 'timelimit' => 1800],
            ['name' => 'Kiểm tra An toàn PCCC', 'course_idx' => 3, 'timelimit' => 1200],
        ];

        foreach ($quizzes as $quiz) {
            $courseId = $this->createdCourseIds[$quiz['course_idx']] ?? $this->createdCourseIds[0];
            $quizId = $this->createQuiz($quiz, $courseId);
            $this->createdQuizIds[] = $quizId;

            // Register in TMS
            DB::connection('mysql_tms')->table('exams')->insert([
                'moodle_course_id' => $courseId,
                'moodle_quiz_id' => $quizId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->info('   ✓ Created ' . count($this->createdQuizIds) . ' quizzes');
    }

    private function createQuiz(array $data, int $courseId): int
    {
        $time = time();
        DB::insert("
            INSERT INTO mdl_quiz 
            (course, name, intro, introformat, timeopen, timeclose, timelimit, 
             overduehandling, graceperiod, preferredbehaviour, canredoquestions, 
             attempts, attemptonlast, grademethod, decimalpoints, questiondecimalpoints,
             reviewattempt, reviewcorrectness, reviewmaxmarks, reviewmarks,
             reviewspecificfeedback, reviewgeneralfeedback, reviewrightanswer,
             reviewoverallfeedback, questionsperpage, navmethod, shuffleanswers,
             sumgrades, grade, timecreated, timemodified)
            VALUES (?, ?, '', 0, 0, 0, ?, 'autoabandon', 0, 'deferredfeedback', 0, 1, 0, 1, 2, -1,
                    1023, 511, 7, 5, 3, 3, 3, 3, 0, 'free', 0, 0, 100, ?, ?)
        ", [
            $courseId,
            $data['name'],
            $data['timelimit'] ?? 1800,
            $time,
            $time
        ]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        return (int)$result[0]->id;
    }

    private function seedEnrollments(): void
    {
        $this->info('8. Tạo Enrollments...');

        $enrolled = 0;
        foreach ($this->createdCourseIds as $courseId) {
            foreach ($this->createdUserIds as $userId) {
                $this->enrolUser($userId, $courseId);
                $enrolled++;
            }
        }

        $this->info('   ✓ Enrolled ' . $enrolled . ' users into courses');
    }

    private function enrolUser(int $userId, int $courseId): bool
    {
        $enrol = DB::selectOne("
            SELECT id FROM mdl_enrol WHERE courseid = ? AND enrol = 'manual'
        ", [$courseId]);

        if (!$enrol) {
            $time = time();
            DB::insert("
                INSERT INTO mdl_enrol (enrol, status, courseid, roleid, sortorder, timecreated, timemodified)
                VALUES ('manual', 0, ?, 5, 0, ?, ?)
            ", [$courseId, $time, $time]);

            $enrol = DB::selectOne("
                SELECT id FROM mdl_enrol WHERE courseid = ? AND enrol = 'manual'
            ", [$courseId]);
        }

        if (!$enrol) {
            return false;
        }

        $existing = DB::selectOne("
            SELECT id FROM mdl_user_enrolments WHERE userid = ? AND enrolid = ?
        ", [$userId, $enrol->id]);

        if ($existing) {
            return true;
        }

        $time = time();
        DB::insert("
            INSERT INTO mdl_user_enrolments (userid, enrolid, status, timestart, timeend, timecreated, timemodified)
            VALUES (?, ?, 0, ?, 0, ?, ?)
        ", [$userId, $enrol->id, $time, $time, $time]);

        return true;
    }

    private function cleanDemoData(): void
    {
        $this->line('   Cleaning demo users...');
        DB::delete("DELETE FROM mdl_user WHERE username LIKE 'user_demo_%'");

        $this->line('   Cleaning demo courses from TMS...');
        DB::connection('mysql_tms')->table('courses')->where('moodle_course_id', '>', 0)->delete();

        $this->line('   Cleaning demo exams from TMS...');
        DB::connection('mysql_tms')->table('exams')->where('moodle_quiz_id', '>', 0)->delete();

        $this->line('   Cleaning demo question sets...');
        DB::delete("DELETE FROM mdl_question_set_questions WHERE set_id IN (SELECT id FROM mdl_question_set WHERE name LIKE 'Bộ đề%')");
        DB::delete("DELETE FROM mdl_question_set WHERE name LIKE 'Bộ đề%'");

        $this->line('   Cleaning demo questions...');
        DB::delete("DELETE FROM mdl_question WHERE name LIKE '%PHP%' OR name LIKE '%Python%' OR name LIKE '%Giao tiếp%' OR name LIKE '%PCCC%'");

        $this->line('   Cleaning demo quizzes...');
        DB::delete("DELETE FROM mdl_quiz WHERE name LIKE 'Kiểm tra%'");

        $this->line('   Cleaning demo enrolments...');
        DB::delete("DELETE FROM mdl_user_enrolments WHERE userid IN (SELECT id FROM mdl_user WHERE username LIKE 'user_demo_%')");
    }

    private function seedPermissions(): void
    {
        $this->info('11. Tạo Permissions...');

        try {
            $roles = [
                ['name' => 'admin', 'display_name' => 'Quản trị viên', 'description' => 'Quản lý hệ thống', 'is_system_role' => 0],
                ['name' => 'trainer', 'display_name' => 'Giảng viên', 'description' => 'Giảng dạy khóa học', 'is_system_role' => 0],
                ['name' => 'learner', 'display_name' => 'Học viên', 'description' => 'Tham gia học tập', 'is_system_role' => 0],
            ];

            foreach ($roles as $role) {
                try {
                    $exists = DB::connection('mysql')->table('mdl_local_permission_roles')
                        ->where('name', $role['name'])->exists();
                    if (!$exists) {
                        DB::connection('mysql')->table('mdl_local_permission_roles')->insert([
                            'name' => $role['name'],
                            'display_name' => $role['display_name'],
                            'description' => $role['description'],
                            'is_system_role' => $role['is_system_role'],
                            'visible' => 1,
                            'created_at' => now(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Skip
                }
            }
        } catch (\Throwable $e) {
            // Skip entire section
        }
        $this->info('   ✓ Done');
    }

    private function seedOrganizationUnits(): void
    {
        $this->info('12. Tạo Organization Units...');

        try {
            $units = [
                ['name' => 'Công ty A', 'code' => 'COMP_A', 'type' => 'company', 'parent' => 0],
                ['name' => 'Phòng IT', 'code' => 'DEPT_IT', 'type' => 'department', 'parent' => 0],
                ['name' => 'Phòng HR', 'code' => 'DEPT_HR', 'type' => 'department', 'parent' => 0],
                ['name' => 'Team Dev', 'code' => 'TEAM_DEV', 'type' => 'team', 'parent' => 0],
            ];

            foreach ($units as $unit) {
                try {
                    $exists = DB::connection('mysql')->table('mdl_org_unit')
                        ->where('code', $unit['code'])->exists();
                    if (!$exists) {
                        DB::connection('mysql')->table('mdl_org_unit')->insert([
                            'name' => $unit['name'],
                            'code' => $unit['code'],
                            'type' => $unit['type'],
                            'parent' => $unit['parent'],
                            'visible' => 1,
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Skip
                }
            }
        } catch (\Throwable $e) {
            // Skip entire section
        }
        $this->info('   ✓ Done');
    }
}
