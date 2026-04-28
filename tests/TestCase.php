<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // CreatesApplication is automatically handled in Laravel 11+
    
    protected function setUp(): void
    {
        parent::setUp();
        // Any additional setup
    }
}