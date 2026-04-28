<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class QuestionSetLogicTest extends TestCase
{
    private function getFilteredSets(array $sets, string $search): array
    {
        if (empty($search)) {
            return $sets;
        }

        $search = strtolower($search);

        return array_filter($sets, function ($set) use ($search) {
            return strpos(strtolower($set['name'] ?? ''), $search) !== false
                || strpos(strtolower($set['description'] ?? ''), $search) !== false;
        });
    }

    public function test_filter_finds_by_set_name(): void
    {
        $sets = [
            ['name' => 'Bộ đề PHP cơ bản', 'description' => 'Các câu hỏi cơ bản'],
            ['name' => 'Bộ đề Python nâng cao', 'description' => 'Các câu hỏi nâng cao'],
        ];

        $result = $this->getFilteredSets($sets, 'php');
        $this->assertCount(1, $result);
    }

    public function test_filter_finds_by_description(): void
    {
        $sets = [
            ['name' => 'Bộ đề 1', 'description' => 'Cơ bản'],
            ['name' => 'Bộ đề 2', 'description' => 'Nâng cao'],
        ];

        $result = $this->getFilteredSets($sets, 'cơ bản');
        $this->assertCount(1, $result);
    }

    public function test_filter_returns_all_when_empty_search(): void
    {
        $sets = [
            ['name' => 'Bộ đề 1', 'description' => 'Mô tả'],
            ['name' => 'Bộ đề 2', 'description' => 'Mô tả'],
        ];

        $result = $this->getFilteredSets($sets, '');
        $this->assertCount(2, $result);
    }

    public function test_filter_is_case_insensitive(): void
    {
        $sets = [
            ['name' => 'Bộ đề PHP', 'description' => 'Mô tả'],
        ];

        $result = $this->getFilteredSets($sets, 'bộ');
        $this->assertCount(1, $result);
    }

    public function test_filter_handles_missing_description(): void
    {
        $sets = [
            ['name' => 'Bộ đề 1'],
            ['name' => 'Bộ đề 2', 'description' => 'Mô tả'],
        ];

        $result = $this->getFilteredSets($sets, 'Bộ');
        $this->assertCount(2, $result);
    }

    public function test_question_count_calculation(): void
    {
        $sets = [
            ['name' => 'Set A', 'question_count' => 10],
            ['name' => 'Set B', 'question_count' => 5],
        ];

        $total = array_sum(array_column($sets, 'question_count'));
        $this->assertEquals(15, $total);
    }

    public function test_question_id_extraction(): void
    {
        $setQuestions = [
            (object) ['question_id' => 1, 'question_name' => 'Q1'],
            (object) ['question_id' => 2, 'question_name' => 'Q2'],
            (object) ['question_id' => 3, 'question_name' => 'Q3'],
        ];

        $ids = array_map(function ($q) {
            return $q->question_id;
        }, $setQuestions);
        $this->assertEquals([1, 2, 3], $ids);
    }
}
