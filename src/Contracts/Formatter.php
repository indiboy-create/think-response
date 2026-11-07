<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse\Contracts;

use think\Paginator;
use think\Response as ThinkResponse;

interface Formatter
{
    public function success($data = null, string $message = '', int $code = 200, array $headers = [], array $options = []): ThinkResponse;

    public function fail(string $message = '', int $code = 500, $errors = null, array $headers = [], array $options = []): ThinkResponse;

    public function format($data = null, string $message = '', int $code = 200, $errors = null, bool $failed = false): array;

    public function paginate(Paginator $paginator): array;
}