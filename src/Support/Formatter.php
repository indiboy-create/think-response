<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse\Support;

use think\contract\Arrayable;
use think\facade\Lang;
use think\helper\Arr;
use think\Paginator;
use think\Response as ThinkResponse;
use Throwable;
use Youvision\ThinkResponse\Contracts\Formatter as FormatterContract;

class Formatter implements FormatterContract
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_replace_recursive(static::defaultConfig(), $config);
    }

    public function success($data = null, string $message = '', int $code = 200, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->build($data, $message, $code, null, false, $headers, $options);
    }

    public function fail(string $message = '', int $code = 500, $errors = null, array $headers = [], array $options = []): ThinkResponse
    {
        return $this->build(null, $message, $code, $errors, true, $headers, $options);
    }

    public function format($data = null, string $message = '', int $code = 200, $errors = null, bool $failed = false): array
    {
        $payload = [
            'status'  => Status::businessStatus($code),
            'code'    => $code,
            'message' => $this->resolveMessage($code, $message, $failed),
            'data'    => $this->formatData($data),
            'error'   => $this->formatError($errors),
        ];

        return $this->applyFields($payload);
    }

    public function paginate(Paginator $paginator): array
    {
        $key   = (string) ($this->config['paginate_key'] ?? 'data');
        $items = $this->normalize($paginator->items());

        return [
            $key   => $items ?? [],
            'meta' => [
                'pagination' => [
                    'count'        => $paginator->count(),
                    'per_page'     => $paginator->listRows(),
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'total'        => $this->paginatorTotal($paginator),
                    'has_more'     => $paginator->hasMore(),
                ],
            ],
        ];
    }

    protected function build($data, string $message, int $code, $errors, bool $failed, array $headers, array $options): ThinkResponse
    {
        $payload = $this->format($data, $message, $code, $errors, $failed);

        return ThinkResponse::create($payload, 'json', $this->httpStatus($code, $failed))
            ->header($headers)
            ->options($options);
    }

    protected function httpStatus(int $code, bool $failed): int
    {
        if ($failed) {
            $errorCode = (int) ($this->config['error_code'] ?? 0);

            if (in_array($errorCode, [200, 500], true)) {
                return $errorCode;
            }
        }

        return Status::httpPrefix($code);
    }

    protected function resolveMessage(int $code, string $message, bool $failed): string
    {
        if ($message !== '') {
            return $message;
        }

        $langMessage = $this->langMessage($code);

        if ($langMessage !== null) {
            return $langMessage;
        }

        $messages = (array) ($this->config['messages'] ?? []);

        foreach ([$code, (string) $code] as $key) {
            if (isset($messages[$key]) && is_string($messages[$key]) && $messages[$key] !== '') {
                return $messages[$key];
            }
        }

        $httpStatus = Status::httpPrefix($code);

        if (isset(Status::MESSAGES[$httpStatus])) {
            return Status::MESSAGES[$httpStatus];
        }

        $defaults = (array) ($this->config['default_message'] ?? []);

        return $failed
            ? (string) ($defaults['fail'] ?? '操作失败')
            : (string) ($defaults['success'] ?? '操作成功');
    }

    protected function langMessage(int $code): ?string
    {
        $lang = trim((string) ($this->config['lang'] ?? ''));

        $keys = [(string) $code];

        if ($lang !== '') {
            $keys = [$lang . '.' . $code, $lang . '_' . $code, ...$keys];
        }

        foreach ($keys as $key) {
            try {
                if (Lang::has($key)) {
                    $message = Lang::get($key);

                    if (is_string($message) && $message !== '') {
                        return $message;
                    }
                }
            } catch (Throwable) {
            }
        }

        return null;
    }

    protected function formatData($data)
    {
        if ($data instanceof Paginator) {
            return $this->paginate($data);
        }

        $data = $this->normalize($data);

        if ($data === null || (is_array($data) && $data === [])) {
            return (object) [];
        }

        return $data;
    }

    protected function formatError($errors)
    {
        if ($errors === null || $errors === '' || $errors === []) {
            return (object) [];
        }

        return $this->normalize($errors);
    }

    protected function normalize($value)
    {
        if ($value instanceof Arrayable) {
            return $this->normalize($value->toArray());
        }

        if ($value instanceof \JsonSerializable) {
            return $this->normalize($value->jsonSerialize());
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->normalize($value->toArray());
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalize($item);
            }
        }

        return $value;
    }

    protected function paginatorTotal(Paginator $paginator): ?int
    {
        try {
            return $paginator->total();
        } catch (Throwable) {
            return null;
        }
    }

    protected function applyFields(array $payload): array
    {
        foreach ((array) ($this->config['fields'] ?? []) as $field => $options) {
            $options = is_array($options) ? $options : ['show' => (bool) $options];
            $show    = (bool) ($options['show'] ?? true);
            $alias   = (string) ($options['alias'] ?? $field);

            if (str_contains($field, '.')) {
                if (!Arr::has($payload, $field)) {
                    continue;
                }

                if (!$show) {
                    Arr::forget($payload, $field);
                    continue;
                }

                if ($alias !== $field) {
                    $value = Arr::get($payload, $field);
                    Arr::forget($payload, $field);
                    Arr::set($payload, $alias, $value);
                }

                continue;
            }

            if (!array_key_exists($field, $payload)) {
                continue;
            }

            if (!$show) {
                unset($payload[$field]);
                continue;
            }

            if ($alias !== $field) {
                $payload[$alias] = $payload[$field];
                unset($payload[$field]);
            }
        }

        return $payload;
    }

    protected static function defaultConfig(): array
    {
        return [
            'error_code'      => false,
            'fields'          => [],
            'paginate_key'    => 'data',
            'messages'        => [],
            'lang'            => 'response',
            'default_message' => [
                'success' => '操作成功',
                'fail'    => '操作失败',
            ],
        ];
    }
}