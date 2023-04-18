<?php

namespace Tests\Unit\Library\Http\Router;

use Library\Http\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected Router $router;

    protected function setUp(): void
    {
        $this->router = new Router(__DIR__ . '/Stub/Case1');
    }

    // 测试获取扫描到的类
    public function testGetClasses(): void
    {
        $router = (new Router(__DIR__ . '/Stub/Case2'));
        $router->load();

        $this->assertSame(
            [
                \Tests\Unit\Library\Http\Router\Stub\Case2\Route1::class,
                \Tests\Unit\Library\Http\Router\Stub\Case2\Route2::class,
            ],
            array_keys($router->getClasses())
        );
    }

    // 测试为定义路由方法，路由为空
    public function testEmptyRouteTree()
    {
        $router = (new Router(__DIR__ . '/Stub/Case2'));
        $router->load();

        $this->assertSame(
            [],
            $router->getRouteTree()
        );
    }

    // 测试路由树比对
    public function testCheckRouteTree()
    {
        $this->router->load();
        // 用来查看路由树生成情况
        $this->assertSame([], $this->router->getRouteTree());
    }

    // 测试路由中间件
    public function testRouteMiddleware()
    {
        $this->router->load();

    }
}
