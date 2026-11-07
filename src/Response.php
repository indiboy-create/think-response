<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse;

use think\Response as ThinkResponse;
use Youvision\ThinkResponse\Contracts\Formatter;

class Response
{
    public function __construct(protected Formatter $formatter)
    {
    }

    public function success($data = null, string $message = '', int $code = 200, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->formatter->success($data, $message, $code, $headers, $options);
    }

    public function ok(string $message = '', int $code = 200, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->success([], $message, $code, $headers, $options);
    }

    public function localize(int $code = 200, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->ok('', $code, $headers, $options);
    }

    public function created($data = null, string $message = '', string $location = '', array $headers = [], array $options = []): ThinkResponse
    {
        return $this->success($data, $message, 201, $this->locationHeader($location, $headers), $options);
    }

    public function accepted($data = null, string $message = '', string $location = '', array $headers = [], array $options = []): ThinkResponse
    {
        return $this->success($data, $message, 202, $this->locationHeader($location, $headers), $options);
    }

    public function noContent(string $message = '', array $headers = [], array $options = []): ThinkResponse
    {
        return $this->success([], $message, 204, $headers, $options);
    }

    public function fail(string $message = '', int $code = 500, $errors = null, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->formatter->fail($message, $code, $errors, $headers, $options);
    }

    public function errorBadRequest(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, 400, $errors, $headers);
    }

    public function errorUnauthorized(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, 401, $errors, $headers);
    }

    public function errorForbidden(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, 403, $errors, $headers);
    }

    public function errorNotFound(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, 404, $errors, $headers);
    }

    public function errorMethodNotAllowed(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, 405, $errors, $headers);
    }

    public function errorInternal(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, 500, $errors, $headers);
    }

    protected function locationHeader(string $location, array $headers): array
    {
        if ($location !== '') {
            $headers['Location'] = $location;
        }

        return $headers;
    }
}