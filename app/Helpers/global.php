<?php

require_once __DIR__ . '/LangHelper.php';

if (!function_exists('__t')) {
    function __t($key, $locale = null)
    {
        return LangHelper::get($key, $locale);
    }
}
