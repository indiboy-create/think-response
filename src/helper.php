<?php

declare(strict_types=1);

use think\Response as ThinkResponse;
use Youvision\ThinkResponse\Response;

if (!function_exists('json_success')) {
    function json_success($data = null, string $message = '', int $code = 200, array $headers = [], array $options = []): ThinkResponse
    {
        return app(Response::class)->success($data, $message, $code, $headers, $options);
    }
}

if (!function_exists('json_fail')) {
    function json_fail(string $message = '', int $code = 500, $errors = null, array $headers = [], array $options = []): ThinkResponse
    {
        return app(Response::class)->fail($message, $code, $errors, $headers, $options);
    }
}