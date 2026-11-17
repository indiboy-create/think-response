<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse;

use think\Response as ThinkResponse;
use Youvision\ThinkResponse\Contracts\Formatter;
use youvision\enum\think\support\enums\HttpStatusCode;

class Response
{
    public function __construct(protected Formatter $formatter)
    {
    }

    public function success($data = null, string $message = '', int $code = HttpStatusCode::HTTP_OK->value, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->formatter->success($data, $message, $code, $headers, $options);
    }

    public function ok(string $message = '', int $code = HttpStatusCode::HTTP_OK->value, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->success([], $message, $code, $headers, $options);
    }

    public function localize(int $code = HttpStatusCode::HTTP_OK->value, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->ok('', $code, $headers, $options);
    }

    public function created($data = null, string $message = '', string $location = '', array $headers = [], array $options = []): ThinkResponse
    {
        return $this->success($data, $message, HttpStatusCode::HTTP_CREATED->value, $this->locationHeader($location, $headers), $options);
    }

    public function accepted($data = null, string $message = '', string $location = '', array $headers = [], array $options = []): ThinkResponse
    {
        return $this->success($data, $message, HttpStatusCode::HTTP_ACCEPTED->value, $this->locationHeader($location, $headers), $options);
    }

    public function noContent(string $message = '', array $headers = [], array $options = []): ThinkResponse
    {
        return $this->success([], $message, HttpStatusCode::HTTP_NO_CONTENT->value, $headers, $options);
    }

    public function fail(string $message = '', int $code = HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR->value, $errors = null, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->formatter->fail($message, $code, $errors, $headers, $options);
    }

    public function errorBadRequest(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, HttpStatusCode::HTTP_BAD_REQUEST->value, $errors, $headers);
    }

    public function errorUnauthorized(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, HttpStatusCode::HTTP_UNAUTHORIZED->value, $errors, $headers);
    }

    public function errorForbidden(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, HttpStatusCode::HTTP_FORBIDDEN->value, $errors, $headers);
    }

    public function errorNotFound(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, HttpStatusCode::HTTP_NOT_FOUND->value, $errors, $headers);
    }

    public function errorMethodNotAllowed(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, HttpStatusCode::HTTP_METHOD_NOT_ALLOWED->value, $errors, $headers);
    }

    public function errorInternal(string $message = '', $errors = null, array $headers = []): ThinkResponse
    {
        return $this->fail($message, HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR->value, $errors, $headers);
    }

    protected function locationHeader(string $location, array $headers): array
    {
        if ($location !== '') {
            $headers['Location'] = $location;
        }

        return $headers;
    }
}