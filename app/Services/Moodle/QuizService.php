<?php

namespace App\Services\Moodle;

use Illuminate\Support\Facades\DB;

class QuizService
{
    public static function getTmsExams(): array
    {
        $activeQuizIds = DB::connection('mysql_tms')->table('exams')
            ->where('status', 'active')
            ->pluck('moodle_quiz_id')
            ->toArray();

        if (empty($activeQuizIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($activeQuizIds), '?'));

        $quizzes = DB::select("
            SELECT q.*, c.fullname as course_name, c.id as course, cm.id as cmid, tc.type as course_type
            FROM mdl_quiz q
            JOIN mdl_course c ON q.course = c.id
            LEFT JOIN mdl_course_modules cm ON cm.instance = q.id AND cm.module = (SELECT id FROM mdl_modules WHERE name = 'quiz')
            LEFT JOIN tms_courses tc ON c.id = tc.moodle_course_id AND tc.status = 'active'
            WHERE q.id IN ({$placeholders})
            ORDER BY q.name ASC
        ", $activeQuizIds);

        foreach ($quizzes as $quiz) {
            $quiz->participants = self::getQuizParticipants($quiz->id);
            $quiz->completed = self::getQuizCompleted($quiz->id);
            $quiz->status = self::getQuizStatus($quiz);
        }

        return $quizzes;
    }

    public static function getQuizParticipants(int $quizId): int
    {
        $quiz = self::getQuiz($quizId);
        if (!$quiz) {
            return 0;
        }
        $courseId = $quiz->course_id ?? $quiz->course;
        $enrolled = CourseService::getEnrolledUsers($courseId);
        return count($enrolled);
    }

    public static function getQuizCompleted(int $quizId): int
    {
        $result = DB::connection('mysql')->select("
            SELECT COUNT(DISTINCT user_id) as count
            FROM quiz_attempts
            WHERE quiz_id = ? AND completed_at IS NOT NULL
        ", [$quizId]);
        return $result[0]->count ?? 0;
    }

    public static function getQuizStatus($quiz): string
    {
        $now = time();
        if ($quiz->timeopen > 0 && $now < $quiz->timeopen) {
            return 'upcoming';
        }
        if ($quiz->timeclose > 0 && $now > $quiz->timeclose) {
            return 'closed';
        }
        return 'active';
    }

    public static function getQuizAttempts(): array
    {
        return DB::select("
            SELECT qa.*, q.name as quiz_name, u.firstname, u.lastname
            FROM mdl_quiz_attempts qa
            JOIN mdl_quiz q ON qa.quiz = q.id
            JOIN mdl_user u ON qa.userid = u.id
            WHERE u.deleted = 0
            ORDER BY qa.timemodified DESC
        ");
    }

    public static function getQuizzes(): array
    {
        return DB::select("
            SELECT q.*, c.fullname as course_name, cm.id as cmid
            FROM mdl_quiz q
            JOIN mdl_course c ON q.course = c.id
            LEFT JOIN mdl_course_modules cm ON cm.instance = q.id AND cm.module = (SELECT id FROM mdl_modules WHERE name = 'quiz')
            ORDER BY q.name ASC
        ");
    }

    public static function getQuiz(int $id): ?object
    {
        $results = DB::select("
            SELECT q.*, c.id as course_id, c.fullname as course_name, tc.type as course_type
            FROM mdl_quiz q
            JOIN mdl_course c ON q.course = c.id
            LEFT JOIN tms_courses tc ON c.id = tc.moodle_course_id AND tc.status = 'active'
            WHERE q.id = ?
        ", [$id]);
        return $results[0] ?? null;
    }

    public static function createQuiz(array $data): int
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return 0;
        }
        $time = time();

        DB::insert("
            INSERT INTO mdl_quiz
            (course, name, intro, introformat, timeopen, timeclose, timelimit,
             overduehandling, graceperiod, preferredbehaviour, canredoquestions,
             attempts, attemptonlast, grademethod, decimalpoints, questiondecimalpoints,
             reviewattempt, reviewcorrectness, reviewmaxmarks, reviewmarks,
             reviewspecificfeedback, reviewgeneralfeedback, reviewrightanswer,
             reviewoverallfeedback, questionsperpage, navmethod, shuffleanswers,
             sumgrades, grade, timecreated, timemodified, password, subnet,
             browsersecurity, delay1, delay2, showuserpicture, showblocks,
             completionattemptsexhausted, completionminattempts, allowofflineattempts)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['course'],
            $data['name'],
            $data['intro'] ?? '',
            $data['introformat'] ?? 0,
            $data['timeopen'] ?? 0,
            $data['timeclose'] ?? 0,
            $data['timelimit'] ?? 0,
            $data['overduehandling'] ?? 'autoabandon',
            $data['graceperiod'] ?? 0,
            $data['preferredbehaviour'] ?? 'deferredfeedback',
            $data['canredoquestions'] ?? 0,
            $data['attempts'] ?? 1,
            $data['attemptonlast'] ?? 0,
            $data['grademethod'] ?? 1,
            $data['decimalpoints'] ?? 2,
            $data['questiondecimalpoints'] ?? -1,
            $data['reviewattempt'] ?? 1023,
            $data['reviewcorrectness'] ?? 511,
            $data['reviewmaxmarks'] ?? 7,
            $data['reviewmarks'] ?? 5,
            $data['reviewspecificfeedback'] ?? 3,
            $data['reviewgeneralfeedback'] ?? 3,
            $data['reviewrightanswer'] ?? 3,
            $data['reviewoverallfeedback'] ?? 3,
            $data['questionsperpage'] ?? 0,
            $data['navmethod'] ?? 'free',
            $data['shuffleanswers'] ?? 0,
            $data['sumgrades'] ?? 0,
            $data['grade'] ?? 100,
            $time,
            $time,
            $data['password'] ?? '',
            $data['subnet'] ?? '',
            $data['browsersecurity'] ?? '',
            $data['delay1'] ?? 0,
            $data['delay2'] ?? 0,
            $data['showuserpicture'] ?? 0,
            $data['showblocks'] ?? 0,
            $data['completionattemptsexhausted'] ?? 0,
            $data['completionminattempts'] ?? 0,
            $data['allowofflineattempts'] ?? 0
        ]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        $quizId = $result[0]->id;

        if (getenv('MOODLE_WRITE_ENABLED') === 'true') {
            DB::connection('mysql_tms')->table('exams')->insert([
                'moodle_course_id' => $data['course'],
                'moodle_quiz_id' => $quizId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $quizId;
    }

    public static function updateQuiz(int $id, array $data): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }
        $sets = [];
        $values = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = time();
        $values[] = $id;

        DB::update("
            UPDATE mdl_quiz
            SET " . implode(', ', $sets) . ", timemodified = ?
            WHERE id = ?
        ", $values);

        return true;
    }

    public static function deleteQuiz(int $id): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }
        DB::update("
            UPDATE mdl_quiz SET timemodified = ? WHERE id = ?
        ", [time(), $id]);

        DB::connection('mysql_tms')->table('exams')
            ->where('moodle_quiz_id', $id)
            ->update(['status' => 'deleted', 'updated_at' => now()]);

        return true;
    }

    public static function getQuestionsInQuiz(int $quizid): array
    {
        return DB::select("
            SELECT qs.*, q.name, q.qtype as qtype_name
            FROM mdl_quiz_slots qs
            JOIN mdl_question q ON qs.displaynumber = q.id
            WHERE qs.quizid = ?
            ORDER BY qs.slot ASC
        ", [$quizid]);
    }

    public static function addQuestionToQuiz(int $quizid, int $questionid, int $slot = 0, float $mark = 1.0): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }

        if ($slot <= 0) {
            $maxSlot = DB::selectOne("
                SELECT COALESCE(MAX(slot), 0) as max_slot
                FROM mdl_quiz_slots
                WHERE quizid = ?
            ", [$quizid]);
            $slot = (int)$maxSlot->max_slot + 1;
        }

        DB::insert("
            INSERT INTO mdl_quiz_slots
            (quizid, slot, page, displaynumber, maxmark)
            VALUES (?, ?, ?, ?, ?)
        ", [$quizid, $slot, $slot, $questionid, $mark]);

        return true;
    }

    public static function removeQuestionFromQuiz(int $slotid): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }
        DB::delete('DELETE FROM mdl_quiz_slots WHERE id = ?', [$slotid]);
        return true;
    }

    public static function getQuestions(): array
    {
        return DB::select("
            SELECT q.id, q.parent, q.name, q.questiontext, q.questiontextformat, q.defaultmark, q.qtype, q.timecreated, q.timemodified,
                   CASE q.qtype
                       WHEN 'essay' THEN 'Essay'
                       WHEN 'multichoice' THEN 'Multiple Choice'
                       WHEN 'truefalse' THEN 'True/False'
                       WHEN 'shortanswer' THEN 'Short Answer'
                       WHEN 'match' THEN 'Matching'
                       ELSE q.qtype
                   END as qtype_name
            FROM mdl_question q
            WHERE q.parent = 0
            ORDER BY q.name ASC
        ");
    }

    public static function getQuestion(int $id): ?object
    {
        $results = DB::select("
            SELECT q.*, qt.name as qtype_name
            FROM mdl_question q
            JOIN mdl_question qt ON q.qtype = qt.qtype
            WHERE q.id = ?
        ", [$id]);
        return $results[0] ?? null;
    }

    public static function getQuestionTypes(): array
    {
        return DB::select('SELECT qtype, name FROM mdl_question ORDER BY name ASC');
    }

    public static function createQuestion(array $data): int
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return 0;
        }
        $time = time();

        DB::insert("
            INSERT INTO mdl_question
            (category, qtype, name, questiontext, questiontextformat, defaultmark, timecreated, timemodified)
            VALUES (?, ?, ?, ?, 1, ?, ?, ?)
        ", [
            $data['category'] ?? 1,
            $data['qtype'] ?? 'essay',
            $data['name'] ?? 'New Question',
            $data['questiontext'] ?? '',
            $data['defaultmark'] ?? 1,
            $time,
            $time
        ]);

        return DB::getPdo()->lastInsertId();
    }

    public static function updateQuestion(int $id, array $data): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return false;
        }

        DB::update("
            UPDATE mdl_question
            SET name = ?, questiontext = ?, qtype = ?, defaultmark = ?, timemodified = ?
            WHERE id = ?
        ", [
            $data['name'] ?? 'Question',
            $data['questiontext'] ?? '',
            $data['qtype'] ?? 'essay',
            $data['defaultmark'] ?? 1,
            time(),
            $id
        ]);

        return true;
    }

    public static function deleteQuestion(int $id): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }
        DB::delete('DELETE FROM mdl_question WHERE id = ?', [$id]);
        return true;
    }

    public static function getQuestionSets(): array
    {
        return DB::select("
            SELECT qs.*,
                   (SELECT COUNT(*) FROM mdl_question_set_questions WHERE set_id = qs.id) as question_count
            FROM mdl_question_set qs
            ORDER BY qs.name ASC
        ");
    }

    public static function getQuestionSet(int $id): ?object
    {
        $results = DB::select('SELECT * FROM mdl_question_set WHERE id = ?', [$id]);
        return $results[0] ?? null;
    }

    public static function getQuestionsInSet(int $setId): array
    {
        return DB::select("
            SELECT qsq.*, q.name as question_name, q.qtype
            FROM mdl_question_set_questions qsq
            JOIN mdl_question q ON qsq.question_id = q.id
            WHERE qsq.set_id = ?
            ORDER BY qsq.position ASC
        ", [$setId]);
    }

    public static function createQuestionSet(array $data): int
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return 0;
        }
        $time = time();

        DB::insert("
            INSERT INTO mdl_question_set (name, description, timecreated, timemodified)
            VALUES (?, ?, ?, ?)
        ", [
            $data['name'] ?? 'New Question Set',
            $data['description'] ?? '',
            $time,
            $time
        ]);

        $setId = DB::getPdo()->lastInsertId();

        if (!empty($data['question_ids'])) {
            $position = 1;
            foreach ($data['question_ids'] as $qid) {
                DB::insert("
                    INSERT INTO mdl_question_set_questions (set_id, question_id, position)
                    VALUES (?, ?, ?)
                ", [$setId, $qid, $position++]);
            }
        }

        return $setId;
    }

    public static function updateQuestionSet(int $id, array $data): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return false;
        }

        DB::update("
            UPDATE mdl_question_set
            SET name = ?, description = ?, timemodified = ?
            WHERE id = ?
        ", [
            $data['name'] ?? 'Question Set',
            $data['description'] ?? '',
            time(),
            $id
        ]);

        if (isset($data['question_ids'])) {
            DB::delete('DELETE FROM mdl_question_set_questions WHERE set_id = ?', [$id]);
            $position = 1;
            foreach ($data['question_ids'] as $qid) {
                DB::insert("
                    INSERT INTO mdl_question_set_questions (set_id, question_id, position)
                    VALUES (?, ?, ?)
                ", [$id, $qid, $position++]);
            }
        }

        return true;
    }

    public static function deleteQuestionSet(int $id): bool
    {
        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }
        DB::delete('DELETE FROM mdl_question_set_questions WHERE set_id = ?', [$id]);
        DB::delete('DELETE FROM mdl_question_set WHERE id = ?', [$id]);
        return true;
    }
}
