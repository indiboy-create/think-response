<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse\Tests\Support;

use PHPUnit\Framework\TestCase;
use think\facade\Lang;
use think\Paginator;
use Youvision\ThinkResponse\Support\Formatter;

final class FormatterTest extends TestCase
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

    public function testFormatSuccess(): void
    {
        $formatter = new Formatter(require __DIR__ . '/../../config/response.php');

        $result = $formatter->format(['id' => 1], '', 200101);

        self::assertSame('success', $result['status']);
        self::assertSame(200101, $result['code']);
        self::assertSame('操作成功', $result['message']);
        self::assertSame(['id' => 1], $result['data']);
        self::assertEquals((object) [], $result['error']);
    }

    public function testFormatFail(): void
    {
        $formatter = new Formatter(require __DIR__ . '/../../config/response.php');

        $result = $formatter->format(null, '参数错误', 404001, ['email' => ['邮箱不能为空']], true);

        self::assertSame('error', $result['status']);
        self::assertSame(404001, $result['code']);
        self::assertSame('参数错误', $result['message']);
        self::assertEquals((object) [], $result['data']);
        self::assertSame(['email' => ['邮箱不能为空']], $result['error']);
    }

    public function testExplicitMessageTakesPriority(): void
    {
        Lang::$messages['response_200101'] = '语言包消息';
        $formatter = new Formatter([
            'messages'        => [200101 => '配置消息'],
            'default_message' => ['success' => '默认消息'],
        ]);

        $result = $formatter->format(null, '显式消息', 200101);

        self::assertSame('显式消息', $result['message']);
    }

    public function testLangMessageTakesPriorityOverConfig(): void
    {
        Lang::$messages['response_200101'] = '语言包消息';
        $formatter = new Formatter([
            'messages'        => [200101 => '配置消息'],
            'default_message' => ['success' => '默认消息'],
        ]);

        $result = $formatter->format(null, '', 200101);

        self::assertSame('语言包消息', $result['message']);
    }

    public function testGroupedLangMessage(): void
    {
        Lang::$messages['response.200101'] = '分组语言包消息';
        $formatter = new Formatter();

        $result = $formatter->format(null, '', 200101);

        self::assertSame('分组语言包消息', $result['message']);
    }

    public function testLangMessageLoadsOnDemand(): void
    {
        Lang::$source['response_200101'] = '按需加载消息';
        self::assertFalse(Lang::has('response_200101'), '语言项尚未加载');

        $formatter = new Formatter();
        $result = $formatter->format(null, '', 200101);

        self::assertSame('按需加载消息', $result['message']);
    }

    public function testConfigMessageTakesPriorityOverStatus(): void
    {
        $formatter = new Formatter([
            'lang'            => '',
            'messages'        => [200101 => '配置消息'],
            'default_message' => ['success' => '默认消息'],
        ]);

        $result = $formatter->format(null, '', 200101);

        self::assertSame('配置消息', $result['message']);
    }

    public function testDefaultMessageFallback(): void
    {
        $formatter = new Formatter([
            'lang'            => '',
            'messages'        => [],
            'default_message' => ['success' => '成功兜底', 'fail' => '失败兜底'],
        ]);

        self::assertSame('成功兜底', $formatter->format(null, '', 402001)['message']);
        self::assertSame('失败兜底', $formatter->format(null, '', 402001, null, true)['message']);
    }

    public function testEmptyDataBecomesObject(): void
    {
        $formatter = new Formatter();

        self::assertEquals((object) [], $formatter->format([], '', 200)['data']);
        self::assertEquals((object) [], $formatter->format(null, '', 200)['data']);
    }

    public function testFieldAliasAndHidden(): void
    {
        $formatter = new Formatter([
            'fields' => [
                'message' => ['show' => true, 'alias' => 'msg'],
                'error'   => ['show' => false],
            ],
        ]);

        $result = $formatter->format(['id' => 1], '操作成功', 200);

        self::assertArrayNotHasKey('message', $result);
        self::assertSame('操作成功', $result['msg']);
        self::assertArrayNotHasKey('error', $result);
    }

    public function testNestedFieldAlias(): void
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->method('items')->willReturn([['id' => 1]]);
        $paginator->method('count')->willReturn(1);
        $paginator->method('listRows')->willReturn(10);
        $paginator->method('currentPage')->willReturn(1);
        $paginator->method('lastPage')->willReturn(1);
        $paginator->method('total')->willReturn(1);
        $paginator->method('hasMore')->willReturn(false);

        $formatter = new Formatter([
            'fields' => [
                'data.data' => ['show' => true, 'alias' => 'data.list'],
            ],
        ]);

        $result = $formatter->format($paginator, '', 200);

        self::assertSame([['id' => 1]], $result['data']['list']);
        self::assertArrayHasKey('meta', $result['data']);
    }

    public function testPaginate(): void
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->method('items')->willReturn([['id' => 1]]);
        $paginator->method('count')->willReturn(1);
        $paginator->method('listRows')->willReturn(10);
        $paginator->method('currentPage')->willReturn(2);
        $paginator->method('lastPage')->willReturn(3);
        $paginator->method('total')->willReturn(21);
        $paginator->method('hasMore')->willReturn(true);

        $formatter = new Formatter(require __DIR__ . '/../../config/response.php');
        $result = $formatter->format($paginator, '', 200);

        self::assertSame([['id' => 1]], $result['data']['data']);
        self::assertSame([
            'count'        => 1,
            'per_page'     => 10,
            'current_page' => 2,
            'last_page'    => 3,
            'total'        => 21,
            'has_more'     => true,
        ], $result['data']['meta']['pagination']);
    }
}
