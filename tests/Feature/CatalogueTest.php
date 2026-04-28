<?php

namespace Tests\Feature;

use Tests\TestCase;

class CatalogueTest extends TestCase
{
    public function test_can_view_catalogue(): void
    {
        $response = $this->get('/tms/catalogue');
        $response->assertStatus(200);
    }
}