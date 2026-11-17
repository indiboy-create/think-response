<?php

declare(strict_types=1);

namespace think\facade;

class Lang
{
    /** @var array<string, string> 已加载的语言项 */
    public static array $messages = [];

    /** @var array<string, string> 语言包源文件（未加载） */
    public static array $source = [];

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$messages);
    }

    public static function get(string $key, array $vars = [], string $range = ''): string
    {
        if (!array_key_exists($key, self::$messages) && array_key_exists($key, self::$source)) {
            self::$messages[$key] = self::$source[$key];
        }

        return self::$messages[$key] ?? $key;
    }
}
