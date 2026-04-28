<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UserFilteringTest extends TestCase
{
    private function getFilteredUsers(array $users, string $search): array
    {
        if (empty($search)) {
            return $users;
        }

        $search = strtolower($search);

        return array_filter($users, function ($user) use ($search) {
            return strpos(strtolower(($user['firstname'] ?? '').' '.($user['lastname'] ?? '')), $search) !== false
                || strpos(strtolower($user['email'] ?? ''), $search) !== false
                || strpos(strtolower($user['username'] ?? ''), $search) !== false;
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

    public function test_filter_finds_by_firstname(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Van A', 'email' => 'a@test.com', 'username' => 'usera'],
            ['firstname' => 'Tran', 'lastname' => 'Van B', 'email' => 'b@test.com', 'username' => 'userb'],
        ];

        $result = $this->getFilteredUsers($users, 'nguyen');
        $this->assertCount(1, $result);
    }

    public function test_filter_finds_by_lastname(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Smith', 'email' => 'a@test.com', 'username' => 'usera'],
            ['firstname' => 'Tran', 'lastname' => 'Johnson', 'email' => 'b@test.com', 'username' => 'userb'],
        ];

        $result = $this->getFilteredUsers($users, 'Smith');
        $this->assertCount(1, $result);
    }

    public function test_filter_finds_by_email(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Van A', 'email' => 'a@test.com', 'username' => 'usera'],
            ['firstname' => 'Tran', 'lastname' => 'Van B', 'email' => 'b@test.com', 'username' => 'userb'],
        ];

        $result = $this->getFilteredUsers($users, 'b@test.com');
        $this->assertCount(1, $result);
    }

    public function test_filter_finds_by_username(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Van A', 'email' => 'a@test.com', 'username' => 'usera'],
            ['firstname' => 'Tran', 'lastname' => 'Van B', 'email' => 'b@test.com', 'username' => 'userb'],
        ];

        $result = $this->getFilteredUsers($users, 'userb');
        $this->assertCount(1, $result);
    }

    public function test_filter_returns_all_when_empty_search(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Van A', 'email' => 'a@test.com', 'username' => 'usera'],
            ['firstname' => 'Tran', 'lastname' => 'Van B', 'email' => 'b@test.com', 'username' => 'userb'],
        ];

        $result = $this->getFilteredUsers($users, '');
        $this->assertCount(2, $result);
    }

    public function test_filter_is_case_insensitive(): void
    {
        $users = [
            ['firstname' => 'Nguyen', 'lastname' => 'Van A', 'email' => 'a@test.com', 'username' => 'usera'],
        ];

        $result = $this->getFilteredUsers($users, 'NGUYEN');
        $this->assertCount(1, $result);
    }

    public function test_pagination_returns_correct_page(): void
    {
        $items = range(1, 55);
        $result = $this->paginate($items, 2, 20);

        $this->assertEquals(20, $result['perPage']);
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
}
