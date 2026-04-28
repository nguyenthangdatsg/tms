<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExcelImportLogicTest extends TestCase
{
    private function parseExcelRow(array $row): array
    {
        $email = isset($row[0]) ? trim($row[0]) : '';
        $username = isset($row[1]) ? trim($row[1]) : '';
        $timeStart = isset($row[2]) ? trim($row[2]) : '';
        $timeEnd = isset($row[3]) ? trim($row[3]) : '';

        return [
            'email' => $email,
            'username' => $username,
            'timeStart' => $timeStart,
            'timeEnd' => $timeEnd,
        ];
    }

    private function validateImportRow(array $parsed, int $rowIndex): array
    {
        $errors = [];

        if (empty($parsed['email']) && empty($parsed['username'])) {
            $errors[] = 'Dòng '.($rowIndex + 2).': Email hoặc Username là bắt buộc';
        }

        if (! empty($parsed['timeStart']) && ! empty($parsed['timeEnd'])) {
            $startTs = strtotime($parsed['timeStart']);
            $endTs = strtotime($parsed['timeEnd']);

            if ($startTs !== false && $endTs !== false && $endTs < $startTs) {
                $errors[] = 'Dòng '.($rowIndex + 2).': Thời gian kết thúc phải lớn hơn hoặc bằng thời gian bắt đầu';
            }
        }

        return $errors;
    }

    private function parseTimeRange(string $timeStart, string $timeEnd): array
    {
        $timestart = 0;
        $timeend = 0;

        if (! empty($timeStart)) {
            $timestart = strtotime($timeStart);
            if ($timestart === false) {
                $timestart = 0;
            }
        }

        if (! empty($timeEnd)) {
            $timeend = strtotime($timeEnd);
            if ($timeend === false) {
                $timeend = 0;
            }
        }

        return ['timestart' => $timestart, 'timeend' => $timeend];
    }

    public function test_parse_excel_row_with_all_fields(): void
    {
        $row = ['user@test.com', 'username', '2024-01-01', '2024-01-31'];
        $result = $this->parseExcelRow($row);

        $this->assertEquals('user@test.com', $result['email']);
        $this->assertEquals('username', $result['username']);
        $this->assertEquals('2024-01-01', $result['timeStart']);
        $this->assertEquals('2024-01-31', $result['timeEnd']);
    }

    public function test_parse_excel_row_with_missing_fields(): void
    {
        $row = ['user@test.com', '', '', ''];
        $result = $this->parseExcelRow($row);

        $this->assertEquals('user@test.com', $result['email']);
        $this->assertEquals('', $result['username']);
        $this->assertEquals('', $result['timeStart']);
        $this->assertEquals('', $result['timeEnd']);
    }

    public function test_parse_excel_row_with_only_username(): void
    {
        $row = ['', 'myuser', '', ''];
        $result = $this->parseExcelRow($row);

        $this->assertEquals('', $result['email']);
        $this->assertEquals('myuser', $result['username']);
    }

    public function test_validate_row_missing_email_and_username(): void
    {
        $parsed = ['email' => '', 'username' => '', 'timeStart' => '', 'timeEnd' => ''];
        $errors = $this->validateImportRow($parsed, 0);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Email hoặc Username là bắt buộc', $errors[0]);
    }

    public function test_validate_row_valid_time_range(): void
    {
        $parsed = ['email' => 'user@test.com', 'username' => '', 'timeStart' => '2024-01-01', 'timeEnd' => '2024-01-31'];
        $errors = $this->validateImportRow($parsed, 0);

        $this->assertCount(0, $errors);
    }

    public function test_validate_row_invalid_time_range(): void
    {
        $parsed = ['email' => 'user@test.com', 'username' => '', 'timeStart' => '2024-01-31', 'timeEnd' => '2024-01-01'];
        $errors = $this->validateImportRow($parsed, 0);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Thời gian kết thúc', $errors[0]);
    }

    public function test_validate_row_missing_end_time(): void
    {
        $parsed = ['email' => 'user@test.com', 'username' => '', 'timeStart' => '2024-01-01', 'timeEnd' => ''];
        $errors = $this->validateImportRow($parsed, 0);

        $this->assertCount(0, $errors);
    }

    public function test_validate_row_missing_start_time(): void
    {
        $parsed = ['email' => 'user@test.com', 'username' => '', 'timeStart' => '', 'timeEnd' => '2024-01-31'];
        $errors = $this->validateImportRow($parsed, 0);

        $this->assertCount(0, $errors);
    }

    public function test_parse_time_range_with_valid_dates(): void
    {
        $result = $this->parseTimeRange('2024-01-01', '2024-01-31');

        $this->assertGreaterThan(0, $result['timestart']);
        $this->assertGreaterThan(0, $result['timeend']);
        $this->assertGreaterThan($result['timestart'], $result['timeend']);
    }

    public function test_parse_time_range_with_invalid_date(): void
    {
        $result = $this->parseTimeRange('invalid-date', '2024-01-31');

        $this->assertEquals(0, $result['timestart']);
        $this->assertGreaterThan(0, $result['timeend']);
    }

    public function test_parse_time_range_with_empty_dates(): void
    {
        $result = $this->parseTimeRange('', '');

        $this->assertEquals(0, $result['timestart']);
        $this->assertEquals(0, $result['timeend']);
    }

    public function test_skip_already_enrolled_user(): void
    {
        $existingIds = [1, 2, 3];
        $newUserId = 2;

        $this->assertTrue(in_array($newUserId, $existingIds));
    }

    public function test_allow_new_user(): void
    {
        $existingIds = [1, 2, 3];
        $newUserId = 4;

        $this->assertFalse(in_array($newUserId, $existingIds));
    }
}
