<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse\Support;

final class Status
{
    public const MESSAGES = [
        100 => '继续',
        101 => '切换协议',
        200 => '操作成功',
        201 => '创建成功',
        202 => '已接受',
        204 => '处理成功',
        301 => '永久重定向',
        302 => '临时重定向',
        304 => '未修改',
        400 => '请求参数错误',
        401 => '未登录或登录已过期',
        403 => '没有访问权限',
        404 => '请求的资源不存在',
        405 => '请求方法不允许',
        408 => '请求超时',
        409 => '数据冲突',
        413 => '上传内容过大',
        415 => '不支持的媒体类型',
        422 => '验证失败',
        429 => '请求过于频繁',
        500 => '服务器内部错误',
        501 => '服务未实现',
        502 => '网关错误',
        503 => '服务暂不可用',
        504 => '网关超时',
    ];

    public static function message(int $code, string $default = ''): string
    {
        return self::MESSAGES[$code] ?? ($default !== '' ? $default : '请求处理失败');
    }

    public static function httpPrefix(int $code): int
    {
        $prefix = (int) substr((string) $code, 0, 3);

        return $prefix >= 100 && $prefix <= 599 ? $prefix : 500;
    }

    public static function businessStatus(int $code): string
    {
        $prefix = self::httpPrefix($code);

        return match (true) {
            $prefix >= 400 && $prefix <= 499 => 'error',
            $prefix >= 500 && $prefix <= 599 => 'fail',
            default                          => 'success',
        };
    }
}