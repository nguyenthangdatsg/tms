<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class QuestionBankLogicTest extends TestCase
{
    private function getFilteredQuestions(array $questions, string $search): array
    {
        if (empty($search)) {
            return $questions;
        }

        $search = strtolower($search);

        return array_filter($questions, function ($question) use ($search) {
            return strpos(strtolower($question['name'] ?? ''), $search) !== false
                || strpos(strtolower($question['questiontext'] ?? ''), $search) !== false
                || (isset($question['qtype_name']) && strpos(strtolower($question['qtype_name']), $search) !== false);
        });
    }

    public function test_filter_finds_by_question_name(): void
    {
        $questions = [
            ['name' => 'Câu hỏi về PHP', 'questiontext' => 'Giải thích về biến', 'qtype_name' => 'Essay'],
            ['name' => 'Câu hỏi về Python', 'questiontext' => 'Giải thích về list', 'qtype_name' => 'Multiple Choice'],
        ];

        $result = $this->getFilteredQuestions($questions, 'php');
        $this->assertCount(1, $result);
    }

    public function test_filter_finds_by_question_text(): void
    {
        $questions = [
            ['name' => 'Câu hỏi về PHP', 'questiontext' => 'Giải thích về biến', 'qtype_name' => 'Essay'],
            ['name' => 'Câu hỏi về Python', 'questiontext' => 'Giải thích về list', 'qtype_name' => 'Multiple Choice'],
        ];

        $result = $this->getFilteredQuestions($questions, 'biến');
        $this->assertCount(1, $result);
    }

    public function test_filter_finds_by_qtype(): void
    {
        $questions = [
            ['name' => 'Câu hỏi 1', 'questiontext' => 'Nội dung', 'qtype_name' => 'Essay'],
            ['name' => 'Câu hỏi 2', 'questiontext' => 'Nội dung', 'qtype_name' => 'Multiple Choice'],
        ];

        $result = $this->getFilteredQuestions($questions, 'multiple');
        $this->assertCount(1, $result);
    }

    public function test_filter_returns_all_when_empty_search(): void
    {
        $questions = [
            ['name' => 'Câu hỏi 1', 'questiontext' => 'Nội dung', 'qtype_name' => 'Essay'],
            ['name' => 'Câu hỏi 2', 'questiontext' => 'Nội dung', 'qtype_name' => 'Multiple Choice'],
        ];

        $result = $this->getFilteredQuestions($questions, '');
        $this->assertCount(2, $result);
    }

    public function test_filter_is_case_insensitive(): void
    {
        $questions = [
            ['name' => 'Câu hỏi về PHP', 'questiontext' => 'Giải thích', 'qtype_name' => 'Essay'],
        ];

        $result = $this->getFilteredQuestions($questions, 'câu');
        $this->assertCount(1, $result);
    }

    public function test_filter_handles_missing_questiontext(): void
    {
        $questions = [
            ['name' => 'Câu hỏi 1', 'qtype_name' => 'Essay'],
            ['name' => 'Câu hỏi 2', 'questiontext' => 'Nội dung', 'qtype_name' => 'Multiple Choice'],
        ];

        $result = $this->getFilteredQuestions($questions, 'Câu');
        $this->assertCount(2, $result);
    }
}
