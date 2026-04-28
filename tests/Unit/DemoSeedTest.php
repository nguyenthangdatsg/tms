<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DemoSeedTest extends TestCase
{
    public function test_demo_seed_command_runs_safely(): void
    {
        // Skip in non-MySQL environments (sqlite tests)
        if (env('DB_CONNECTION') !== 'mysql') {
            $this->markTestSkipped('Demo seed requires MySQL environment. Skipping in sqlite test env.');
        }

        try {
            Artisan::call('demo:seed', ['--fresh' => true]);
        } catch (\Throwable $e) {
            $this->fail('Demo seed threw exception: ' . $e->getMessage());
        }
        $this->assertTrue(true);
    }
}
