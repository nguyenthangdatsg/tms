<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once base_path('app/Helpers/LangHelper.php');
    }

    public function boot(): void
    {
        //
    }
}