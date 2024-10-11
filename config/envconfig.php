<?php
require_once __DIR__ . '/../' . '/vendor/autoload.php';
use Dotenv\Dotenv;

class Env
{
    private static $initialized = false;

    private static function initialize()
    {
        if (!self::$initialized) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
            self::$initialized = true;
        }
    }

    public static function get($key, $default = null)
    {
        self::initialize();
        return $_ENV[$key] ?? $default;
    }
}