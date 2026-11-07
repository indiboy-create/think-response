<?php

declare(strict_types=1);

namespace Youvision\ThinkResponse;

use think\exception\Handle as ThinkHandle;
use think\Service as BaseService;
use Youvision\ThinkResponse\Contracts\Formatter as FormatterContract;
use Youvision\ThinkResponse\Exception\Handle;
use Youvision\ThinkResponse\Support\Formatter;

class Service extends BaseService
{
    public function register(): void
    {
        $this->app->bind(FormatterContract::class, function () {
            return new Formatter((array) $this->app->config->get('response', []));
        });

        $this->app->bind(Response::class, function () {
            return new Response($this->app->make(FormatterContract::class, [], true));
        });

        $this->registerExceptionHandler();
    }

    protected function registerExceptionHandler(): void
    {
        $config = (array) $this->app->config->get('response.exception', []);

        if (!($config['enable'] ?? true)) {
            return;
        }

        $override = (bool) ($config['override'] ?? false);

        if ($override || !$this->app->bound(ThinkHandle::class)) {
            $this->app->bind(ThinkHandle::class, Handle::class);
            $this->app->delete(ThinkHandle::class);
        }
    }
}