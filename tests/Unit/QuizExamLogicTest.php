<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class QuizExamLogicTest extends TestCase
{
    private function getFilteredQuizzes(array $quizzes, string $search): array
    {
        if (empty($search)) {
            return $quizzes;
        }

        $search = strtolower($search);

        return array_filter($quizzes, function ($quiz) use ($search) {
            return strpos(strtolower($quiz['name'] ?? ''), $search) !== false
                || strpos(strtolower($quiz['course_name'] ?? ''), $search) !== false;
        });
    }

    private function paginate(array $items, int $currentPage, int $perPage): array
    {
        $total = count($items);
        $lastPage = max(1, (int) ceil($total / $perPage));

        if ($currentPage > $lastPage) {
            $currentPage = $lastPage;
        }

        $start = ($currentPage - 1) * $perPage;
        $sliced = array_slice($items, $start, $perPage);

        return [
            'items' => $sliced,
            'total' => $total,
            'page' => $currentPage,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
        ];
    }

    private function getQuizStatus(object $quiz): string
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

    public function test_filter_finds_by_quiz_name(): void
    {
        $quizzes = [
            ['name' => 'Giữa kỳ PHP', 'course_name' => 'Lập trình PHP'],
            ['name' => 'Cuối kỳ Python', 'course_name' => 'Lập trình Python'],
        ];

        $result = $this->getFilteredQuizzes($quizzes, 'giữa');
        $this->assertCount(1, $result);
    }

    public function test_filter_finds_by_course_name(): void
    {
        $quizzes = [
            ['name' => 'Giữa kỳ PHP', 'course_name' => 'Lập trình PHP'],
            ['name' => 'Cuối kỳ Python', 'course_name' => 'Lập trình Python'],
        ];

        $result = $this->getFilteredQuizzes($quizzes, 'python');
        $this->assertCount(1, $result);
    }

    public function test_filter_returns_all_when_empty_search(): void
    {
        $quizzes = [
            ['name' => 'Giữa kỳ PHP', 'course_name' => 'Lập trình PHP'],
            ['name' => 'Cuối kỳ Python', 'course_name' => 'Lập trình Python'],
        ];

        $result = $this->getFilteredQuizzes($quizzes, '');
        $this->assertCount(2, $result);
    }

    public function test_filter_is_case_insensitive(): void
    {
        $quizzes = [
            ['name' => 'Giữa kỳ PHP', 'course_name' => 'Lập trình PHP'],
        ];

        $result = $this->getFilteredQuizzes($quizzes, 'giữa');
        $this->assertCount(1, $result);
    }

    public function test_quiz_status_active(): void
    {
        $quiz = (object) [
            'timeopen' => 0,
            'timeclose' => 0,
        ];

        $this->assertEquals('active', $this->getQuizStatus($quiz));
    }

    public function test_quiz_status_upcoming(): void
    {
        $quiz = (object) [
            'timeopen' => time() + 86400,
            'timeclose' => time() + 172800,
        ];

        $this->assertEquals('upcoming', $this->getQuizStatus($quiz));
    }

    public function test_quiz_status_closed(): void
    {
        $quiz = (object) [
            'timeopen' => time() - 172800,
            'timeclose' => time() - 86400,
        ];

        $this->assertEquals('closed', $this->getQuizStatus($quiz));
    }

    public function test_pagination_returns_correct_page(): void
    {
        $items = range(1, 55);
        $result = $this->paginate($items, 2, 20);

        $this->assertEquals(2, $result['page']);
        $this->assertEquals(3, $result['lastPage']);
        $this->assertEquals(55, $result['total']);
        $this->assertCount(20, $result['items']);
    }

    public function test_pagination_adjusts_page_if_exceeds_max(): void
    {
        $items = range(1, 15);
        $result = $this->paginate($items, 10, 10);

        $this->assertEquals(2, $result['page']);
    }

    public function test_pagination_handles_single_page(): void
    {
        $items = range(1, 5);
        $result = $this->paginate($items, 1, 20);

        $this->assertEquals(1, $result['lastPage']);
        $this->assertCount(5, $result['items']);
    }

    public function test_pagination_handles_empty_array(): void
    {
        $result = $this->paginate([], 1, 10);

        $this->assertEquals(1, $result['lastPage']);
        $this->assertEquals(0, $result['total']);
        $this->assertCount(0, $result['items']);
    }
}
