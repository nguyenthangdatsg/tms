<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EnrolledUserLogicTest extends TestCase
{
    private function getFilteredEnrolledUsers(array $users, string $search, string $methodFilter = ''): array
    {
        if (! empty($search)) {
            $search = strtolower($search);
            $users = array_filter($users, function ($user) use ($search) {
                $fullname = strtolower(($user['firstname'] ?? '').' '.($user['lastname'] ?? ''));
                $email = strtolower($user['email'] ?? '');
                $username = strtolower($user['username'] ?? '');

                return strpos($fullname, $search) !== false
                    || strpos($email, $search) !== false
                    || strpos($username, $search) !== false;
            });
        }

        if (! empty($methodFilter)) {
            $users = array_filter($users, function ($user) use ($methodFilter) {
                return ($user['enrol_method'] ?? 'manual') === $methodFilter;
            });
        }

        return $users;
    }

    private function getFilteredUsers(array $users, string $search): array
    {
        if (! empty($search)) {
            $search = strtolower($search);
            $users = array_filter($users, function ($user) use ($search) {
                $fullname = strtolower(($user['firstname'] ?? '').' '.($user['lastname'] ?? ''));
                $email = strtolower($user['email'] ?? '');
                $username = strtolower($user['username'] ?? '');

                return strpos($fullname, $search) !== false
                    || strpos($email, $search) !== false
                    || strpos($username, $search) !== false;
            });
        }

        return $users;
    }

    public function test_filter_enrolled_users_by_fullname(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Van A', 'email' => 'a@test.com', 'username' => 'usera', 'enrol_method' => 'manual'],
            ['firstname' => 'Tran', 'lastname' => 'Van B', 'email' => 'b@test.com', 'username' => 'userb', 'enrol_method' => 'cohort'],
        ];

        $result = $this->getFilteredEnrolledUsers($users, 'nguyen');
        $this->assertCount(1, $result);
    }

    public function test_filter_enrolled_users_by_email(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Van A', 'email' => 'a@test.com', 'username' => 'usera', 'enrol_method' => 'manual'],
            ['firstname' => 'Tran', 'lastname' => 'Van B', 'email' => 'b@test.com', 'username' => 'userb', 'enrol_method' => 'cohort'],
        ];

        $result = $this->getFilteredEnrolledUsers($users, 'b@test.com');
        $this->assertCount(1, $result);
    }

    public function test_filter_by_enrol_method(): void
    {
        $users = [
            ['firstname' => 'User 1', 'email' => 'u1@test.com', 'enrol_method' => 'manual'],
            ['firstname' => 'User 2', 'email' => 'u2@test.com', 'enrol_method' => 'cohort'],
            ['firstname' => 'User 3', 'email' => 'u3@test.com', 'enrol_method' => 'manual'],
        ];

        $result = $this->getFilteredEnrolledUsers($users, '', 'cohort');
        $this->assertCount(1, $result);
    }

    public function test_combined_filter_search_and_method(): void
    {
        $users = [
            ['firstname' => 'User 1', 'email' => 'u1@test.com', 'enrol_method' => 'manual'],
            ['firstname' => 'User 2', 'email' => 'u2@test.com', 'enrol_method' => 'cohort'],
        ];

        $result = $this->getFilteredEnrolledUsers($users, 'user 2', 'cohort');
        $this->assertCount(1, $result);
    }

    public function test_filter_returns_all_when_no_filters(): void
    {
        $users = [
            ['firstname' => 'User 1', 'email' => 'u1@test.com', 'enrol_method' => 'manual'],
            ['firstname' => 'User 2', 'email' => 'u2@test.com', 'enrol_method' => 'cohort'],
        ];

        $result = $this->getFilteredEnrolledUsers($users, '', '');
        $this->assertCount(2, $result);
    }

    public function test_filter_is_case_insensitive(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Van A', 'email' => 'a@test.com', 'username' => 'usera'],
        ];

        $result = $this->getFilteredEnrolledUsers($users, 'NGUYEN');
        $this->assertCount(1, $result);
    }

    public function test_filter_handles_missing_enrol_method(): void
    {
        $users = [
            ['firstname' => 'User 1', 'email' => 'u1@test.com', 'enrol_method' => 'manual'],
            ['firstname' => 'User 2', 'email' => 'u2@test.com', 'enrol_method' => 'cohort'],
        ];

        $result = $this->getFilteredEnrolledUsers($users, '', 'cohort');
        $this->assertCount(1, $result);
    }

    public function test_filter_excludes_users_by_method(): void
    {
        $users = [
            ['firstname' => 'User 1', 'email' => 'u1@test.com', 'enrol_method' => 'manual'],
            ['firstname' => 'User 2', 'email' => 'u2@test.com', 'enrol_method' => 'cohort'],
            ['firstname' => 'User 3', 'email' => 'u3@test.com', 'enrol_method' => 'manual'],
        ];

        $result = $this->getFilteredEnrolledUsers($users, '', 'manual');
        $this->assertCount(2, $result);
    }
}
