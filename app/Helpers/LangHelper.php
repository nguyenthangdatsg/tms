<?php

class LangHelper
{
    private static $translations = [];
    private static $currentLocale = 'vi';
    private static $loaded = false;

    public static function init()
    {
        if (self::$loaded) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $locale = $_SESSION['locale'] ?? 'vi';
        self::$currentLocale = $locale;

        $viFile = dirname(__DIR__, 2) . '/lang/vi/messages.php';
        $enFile = dirname(__DIR__, 2) . '/lang/en/messages.php';

        $vi = file_exists($viFile) ? require $viFile : [];
        $en = file_exists($enFile) ? require $enFile : [];

        self::$translations = [
            'vi' => $vi,
            'en' => $en,
        ];

        self::$loaded = true;
    }

    public static function get($key, $locale = null)
    {
        if (!self::$loaded) {
            self::init();
        }

        $locale = $locale ?: self::$currentLocale;

        return self::$translations[$locale][$key]
            ?? self::$translations['vi'][$key]
            ?? $key;
    }

    public static function setLocale($locale)
    {
        self::$currentLocale = $locale;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['locale'] = $locale;
    }
}

function __t($key)
{
    return LangHelper::get($key);
}