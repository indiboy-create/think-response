<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse\Exception;


use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle as BaseHandle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Request;
use think\Response as ThinkResponse;
use Throwable;
use Youvision\ThinkResponse\Response;
use Youvision\ThinkResponse\Support\Status;

class Handle extends BaseHandle
{

    public function render($request, Throwable $e): ThinkResponse
    {
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        if (!$this->shouldReturnJson($request)) {
            return parent::render($request, $e);
        }

        return $this->renderJson($e);
    }

    protected function shouldReturnJson(Request $request): bool
    {
        if ((bool) $this->exceptionConfig('always', false)) {
            return true;
        }

        if ($request->isJson() || $request->isAjax()) {
            return true;
        }

        return str_contains(strtolower((string) $request->header('accept', '')), 'json');
    }

    protected function renderJson(Throwable $e): ThinkResponse
    {
        [$code, $message, $errors, $headers] = $this->mapException($e);

        return $this->app->make(Response::class)->fail($message, $code, $errors, $headers);
    }

    protected function mapException(Throwable $e): array
    {
        $debug   = $this->debugEnabled();
        $headers = [];
        $errors  = null;
        $code    = 500;
        $message = '';
        $expose  = false;

        if ($e instanceof HttpException) {
            $code    = $e->getStatusCode();
            $message = $e->getMessage();
            $expose  = true;
            $headers = $e->getHeaders();
        } elseif (($options = $this->matchException($e)) !== null) {
            $code    = (int) ($options['code'] ?? 500);
            $message = (string) ($options['message'] ?? '');
            $expose  = (bool) ($options['expose'] ?? false);
        } else {
            $message = $debug ? $e->getMessage() : '';
            $expose  = $debug;
        }

        if ($e instanceof ValidateException) {
            $errors = $e->getError();
        }

        if ($message === '' && ($debug || $expose)) {
            $message = $e->getMessage();
        }

        if ($message === '') {
            $message = Status::message(Status::httpPrefix($code), '服务器内部错误');
        }

        return [$code, $message, $errors, $headers];
    }

    protected function matchException(Throwable $e): ?array
    {
        foreach ((array) $this->exceptionConfig('map', []) as $class => $options) {
            if ($e instanceof $class) {
                return (array) $options;
            }
        }

        return null;
    }

    protected function debugEnabled(): bool
    {
        $debug = $this->exceptionConfig('debug', null);

        return $debug === null ? $this->app->isDebug() : (bool) $debug;
    }

    protected function exceptionConfig(string $key, $default = null)
    {
        $config = (array) $this->app->config->get('response.exception', []);

        $config = array_replace_recursive([
            'enable'   => true,
            'override' => false,
            'always'   => false,
            'debug'    => null,
            'map'      => [
                ValidateException::class         => [
                    'code'    => 422,
                    'message' => '验证失败',
                    'expose'  => true,
                ],
                ModelNotFoundException::class    => [
                    'code'    => 404,
                    'message' => '数据不存在',
                    'expose'  => false,
                ],
                DataNotFoundException::class     => [
                    'code'    => 404,
                    'message' => '数据不存在',
                    'expose'  => false,
                ],
            ],
        ], $config);

        return $config[$key] ?? $default;
    }
}