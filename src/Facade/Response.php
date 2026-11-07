<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse\Facade;

use think\Facade;
use Youvision\ThinkResponse\Response as ResponseService;

/**
 * @method static \think\Response success($data = null, string $message = '', int $code = 200, array $headers = [], array $options = [])
 * @method static \think\Response ok(string $message = '', int $code = 200, array $headers = [], array $options = [])
 * @method static \think\Response localize(int $code = 200, array $headers = [], array $options = [])
 * @method static \think\Response created($data = null, string $message = '', string $location = '', array $headers = [], array $options = [])
 * @method static \think\Response accepted($data = null, string $message = '', string $location = '', array $headers = [], array $options = [])
 * @method static \think\Response noContent(string $message = '', array $headers = [], array $options = [])
 * @method static \think\Response fail(string $message = '', int $code = 500, $errors = null, array $headers = [], array $options = [])
 * @method static \think\Response errorBadRequest(string $message = '', $errors = null, array $headers = [])
 * @method static \think\Response errorUnauthorized(string $message = '', $errors = null, array $headers = [])
 * @method static \think\Response errorForbidden(string $message = '', $errors = null, array $headers = [])
 * @method static \think\Response errorNotFound(string $message = '', $errors = null, array $headers = [])
 * @method static \think\Response errorMethodNotAllowed(string $message = '', $errors = null, array $headers = [])
 * @method static \think\Response errorInternal(string $message = '', $errors = null, array $headers = [])
 *
 * @see \Youvision\ThinkResponse\Response
 */
class Response extends Facade
{
    protected static function getFacadeClass(): string
    {
        return ResponseService::class;
    }
}