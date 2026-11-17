<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse\Tests;

use PHPUnit\Framework\TestCase;
use think\facade\Lang;
use Youvision\ThinkResponse\Support\Status;
use youvision\enum\think\support\enums\HttpStatusCode;

final class StatusTest extends TestCase
{
    protected function setUp(): void
    {
        Lang::$messages = [];
        Lang::$source = [];
    }

    protected function tearDown(): void
    {
        Lang::$messages = [];
        Lang::$source = [];
    }

    public function testBuiltInMessages(): void
    {
        self::assertSame('操作成功', Status::message(200));
        self::assertSame('验证失败', Status::message(422));
        self::assertSame('服务器内部错误', Status::message(500));
    }

    public function testMessageFromLanguagePack(): void
    {
        $key = HttpStatusCode::class . '.402';
        Lang::$messages[$key] = '需要付款';

        self::assertSame('需要付款', Status::message(402, 'fallback'));
    }

    public function testMessageLoadsLanguagePackOnDemand(): void
    {
        $key = HttpStatusCode::class . '.402';
        Lang::$source[$key] = '需要付款';
        self::assertFalse(Lang::has($key), '语言项尚未加载');

        self::assertSame('需要付款', Status::message(402, 'fallback'));
    }

    public function testMessageFallsBackToDefault(): void
    {
        self::assertSame('fallback', Status::message(402, 'fallback'));
        self::assertSame('请求处理失败', Status::message(402));
    }

    public function testHttpPrefix(): void
    {
        self::assertSame(200, Status::httpPrefix(200101));
        self::assertSame(404, Status::httpPrefix(404001));
        self::assertSame(500, Status::httpPrefix(500102));
        self::assertSame(200, Status::httpPrefix(200));
    }

    public function testHttpPrefixFallsBackTo500ForInvalidPrefix(): void
    {
        self::assertSame(500, Status::httpPrefix(299999));
        self::assertSame(500, Status::httpPrefix(0));
        self::assertSame(500, Status::httpPrefix(999));
    }

    public function testBusinessStatus(): void
    {
        self::assertSame('success', Status::businessStatus(200101));
        self::assertSame('error', Status::businessStatus(404001));
        self::assertSame('fail', Status::businessStatus(500102));
    }
}
