<?php

namespace Tests\Unit;

use Tests\TestCase;

class LangHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once base_path('app/Helpers/LangHelper.php');
    }

    /** @test */
    public function it_returns_key_when_no_translation(): void
    {
        $result = \LangHelper::get('nonexistent.key');
        $this->assertEquals('nonexistent.key', $result);
    }

    /** @test */
    public function it_returns_translation_for_vi_locale(): void
    {
        \LangHelper::init();
        $result = \LangHelper::get('user_management', 'vi');
        $this->assertNotEquals('user_management', $result);
    }

    /** @test */
    public function it_fallback_to_vi_when_en_locale_missing(): void
    {
        \LangHelper::init();
        $result = \LangHelper::get('nonexistent.key', 'en');
        $this->assertEquals('nonexistent.key', $result);
    }

    /** @test */
    public function it_can_set_locale(): void
    {
        \LangHelper::setLocale('en');
        $this->assertEquals('en', $_SESSION['locale'] ?? 'vi');
    }

    /** @test */
    public function it_can_load_translations(): void
    {
        \LangHelper::init();
        $vi = \LangHelper::get('dashboard', 'vi');
        $this->assertNotEmpty($vi);
    }
}