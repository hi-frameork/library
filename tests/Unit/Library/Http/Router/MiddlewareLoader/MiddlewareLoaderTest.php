<?php

namespace Tests\Unit\Library\Http\Router\MiddlewareLoader;

use Library\Http\Router\MiddlewareLoader;
use PHPUnit\Framework\TestCase;

/**
 * 中间件加载器测试
 */
class MiddlewareLoaderTest extends TestCase
{
    protected MiddlewareLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new MiddlewareLoader([
            'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLogin',
            'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLog',
            'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\CorsDefault',
            'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\Other',
        ]);
    }

    // 测试加载中间件
    public function testLoadMiddleware()
    {
        $parsed = $this->loader->getParsed();

        // 路由组
        $this->assertSame(
            [
                [
                    'alias'    => 'auth.login',
                    'priority' => 5,
                    'class'    => 'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLogin',
                ],
                [
                    'alias'    => 'auth.log',
                    'priority' => 1,
                    'class'    => 'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLog',
                ],
            ],
            $parsed['auth']
        );

        // 路由
        $this->assertSame(
            [
                'alias'    => 'cors.default',
                'priority' => 0,
                'class'    => 'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\CorsDefault',
            ],
            $parsed['cors.default']
        );
        $this->assertSame(
            [
                'alias'    => 'auth.login',
                'priority' => 5,
                'class'    => 'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLogin',
            ],
            $parsed['auth.login']
        );
        $this->assertSame(
            [
                'alias'    => 'auth.log',
                'priority' => 1,
                'class'    => 'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLog',
            ],
            $parsed['auth.log']
        );
    }

    public function testGetGroup()
    {
        // 测试获取中间件组
        $this->assertSame(
            [
                'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLog',
                'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLogin',
            ],
            $this->loader->get('auth')
        );

        // 测试获取指定单个路由
        $this->assertSame(
            [
                'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLogin',
            ],
            $this->loader->get('auth.login')
        );

        // 测试以数组形式获取中间件组
        $this->assertSame(
            [
                'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\CorsDefault',
                'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLog',
                'Tests\Unit\Library\Http\Router\MiddlewareLoader\Stub\AuthLogin',
            ],
            $this->loader->get(['auth', 'cors.default'])
        );
    }

    // 测试获取不存在的中间件
    public function testGetNotExists()
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \Exception($errstr, $errno);
        }, E_USER_WARNING);

        $this->expectExceptionMessage('中间件定义 \'not-exists\' 不存在');
        $this->loader->get('not-exists');

        restore_error_handler();
    }
}
