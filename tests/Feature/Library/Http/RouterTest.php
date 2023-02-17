<?php

namespace Tests\Feature\Http;

use Library\Http\Router;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RouterTest extends TestCase
{
    // 测试路由类实例化
    public function testConstruct()
    {
        $router = new Router(__DIR__);
        $this->assertSame([__CLASS__], $router->getRouteClasses());
    }

    // 测试加载注解路由
    public function testLoadNormal()
    {
        $router = new Router(basePath('src/transport/http/Routes'));
        $router->load();
        // print_r($router->getRouteClasses());
        // print_r($router->getRouteAttributes());
    }

    // 测试加载空目录路由
    public function testLoadEmptyRouteDir()
    {
        $router = new Router(basePath('public'));
        $router->load();
    }

    // 测试加载不存在目录抛出异常
    public function testLoadNotExistRouteDirWillThrowException()
    {
        $this->expectException(RuntimeException::class);
        $router = new Router(basePath('routes'));
        $router->load();
    }

    public function testLoadClassNoRouteAttributeWillThrowException()
    {
        $router = new Router(__DIR__);

        $this->expectException(RuntimeException::class);

        $router->load();
    }
}
