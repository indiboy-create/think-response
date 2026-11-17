<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse\Tests;

use PHPUnit\Framework\TestCase;
use think\Response as ThinkResponse;
use Youvision\ThinkResponse\Contracts\Formatter;
use Youvision\ThinkResponse\Response;

final class ResponseTest extends TestCase
{
    public function testSuccessDelegatesToFormatter(): void
    {
        $expected = $this->createMock(ThinkResponse::class);
        $formatter = $this->createMock(Formatter::class);
        $formatter->expects(self::once())
            ->method('success')
            ->with(['id' => 1], 'ok', 201, ['X-Test' => '1'], ['json_encode_param' => JSON_UNESCAPED_UNICODE])
            ->willReturn($expected);

        $response = new Response($formatter);
        self::assertSame($expected, $response->success(
            ['id' => 1],
            'ok',
            201,
            ['X-Test' => '1'],
            ['json_encode_param' => JSON_UNESCAPED_UNICODE]
        ));
    }

    public function testFailDelegatesToFormatter(): void
    {
        $expected = $this->createMock(ThinkResponse::class);
        $formatter = $this->createMock(Formatter::class);
        $formatter->expects(self::once())
            ->method('fail')
            ->with('error', 422, ['field' => 'required'], [], [])
            ->willReturn($expected);

        $response = new Response($formatter);
        self::assertSame($expected, $response->fail('error', 422, ['field' => 'required']));
    }

    public function testCreatedSetsLocationHeader(): void
    {
        $formatter = $this->createMock(Formatter::class);
        $formatter->expects(self::once())
            ->method('success')
            ->with(['id' => 1], '', 201, ['Location' => '/users/1'], [])
            ->willReturn($this->createMock(ThinkResponse::class));

        (new Response($formatter))->created(['id' => 1], '', '/users/1');
    }

    public function testCreatedOverridesExistingLocationHeader(): void
    {
        $formatter = $this->createMock(Formatter::class);
        $formatter->expects(self::once())
            ->method('success')
            ->with(null, '', 201, ['Location' => '/ignored'], [])
            ->willReturn($this->createMock(ThinkResponse::class));

        self::assertInstanceOf(
            ThinkResponse::class,
            (new Response($formatter))->created(null, '', '/ignored', ['Location' => '/custom'])
        );
    }

    public function testNoContentUses204(): void
    {
        $formatter = $this->createMock(Formatter::class);
        $formatter->expects(self::once())
            ->method('success')
            ->with([], '', 204, [], [])
            ->willReturn($this->createMock(ThinkResponse::class));

        (new Response($formatter))->noContent();
    }

    public function testErrorHelpersUseExpectedCodes(): void
    {
        $codes = [
            'errorBadRequest'        => 400,
            'errorUnauthorized'      => 401,
            'errorForbidden'         => 403,
            'errorNotFound'          => 404,
            'errorMethodNotAllowed'  => 405,
            'errorInternal'          => 500,
        ];

        foreach ($codes as $method => $code) {
            $formatter = $this->createMock(Formatter::class);
            $formatter->expects(self::once())
                ->method('fail')
                ->with('', $code, null, [])
                ->willReturn($this->createMock(ThinkResponse::class));

            (new Response($formatter))->{$method}();
        }
    }
}
