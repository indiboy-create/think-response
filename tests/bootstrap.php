<?php

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "请先运行 composer install\n");
    exit(1);
}

require $autoload;

if (!class_exists(\think\facade\Lang::class, false)) {
    require __DIR__ . '/Stubs/Lang.php';
}
