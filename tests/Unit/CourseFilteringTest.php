<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CourseFilteringTest extends TestCase
{
    private function getFilteredCourses(array $courses, string $search): array
    {
        if (empty($search)) {
            return $courses;
        }

        $search = strtolower($search);

        return array_filter($courses, function ($course) use ($search) {
            return strpos(strtolower($course['fullname'] ?? ''), $search) !== false
                || (isset($course['shortname']) && strpos(strtolower($course['shortname']), $search) !== false);
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

    public function test_filter_finds_by_fullname(): void
    {
        $courses = [
            ['fullname' => 'Lập trình PHP', 'shortname' => 'PHP101'],
            ['fullname' => 'Lập trình Python', 'shortname' => 'PY101'],
        ];

        $result = $this->getFilteredCourses($courses, 'php');
        $this->assertCount(1, $result);
    }

    public function test_filter_finds_by_shortname(): void
    {
        $courses = [
            ['fullname' => 'Lập trình PHP', 'shortname' => 'PHP101'],
            ['fullname' => 'Lập trình Python', 'shortname' => 'PY101'],
        ];

        $result = $this->getFilteredCourses($courses, 'PY');
        $this->assertCount(1, $result);
    }

    public function test_filter_returns_all_when_empty_search(): void
    {
        $courses = [
            ['fullname' => 'Lập trình PHP', 'shortname' => 'PHP101'],
            ['fullname' => 'Lập trình Python', 'shortname' => 'PY101'],
        ];

        $result = $this->getFilteredCourses($courses, '');
        $this->assertCount(2, $result);
    }

    public function test_filter_is_case_insensitive(): void
    {
        $courses = [
            ['fullname' => 'Lập trình PHP', 'shortname' => 'PHP101'],
        ];

        $result = $this->getFilteredCourses($courses, 'lập');
        $this->assertCount(1, $result);
    }

    public function test_filter_handles_missing_fullname(): void
    {
        $courses = [
            ['shortname' => 'PHP101'],
            ['fullname' => 'Lập trình PHP', 'shortname' => 'PHP101'],
        ];

        $result = $this->getFilteredCourses($courses, 'PHP');
        $this->assertCount(2, $result);
    }

    public function test_filter_handles_missing_shortname(): void
    {
        $courses = [
            ['fullname' => 'Lập trình PHP'],
            ['fullname' => 'Lập trình Python', 'shortname' => 'PY101'],
        ];

        $result = $this->getFilteredCourses($courses, 'PHP');
        $this->assertCount(1, $result);
    }

    public function test_pagination_returns_correct_page(): void
    {
        $items = range(1, 55);
        $result = $this->paginate($items, 2, 20);

        $this->assertEquals(2, $result['page']);
        $this->assertEquals(3, $result['lastPage']);
        $this->assertEquals(55, $result['total']);
        $this->assertCount(20, $result['items']);
        $this->assertEquals(21, $result['items'][0]);
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
