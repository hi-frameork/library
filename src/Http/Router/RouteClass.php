<?php

namespace Library\Http\Router;

use Library\Attribute\Types\Route;

class RouteClass
{
    /**
     * @param RouteClassMethod[] $methods
     */
    public function __construct(
        public string $name,
        public Route $attribute,
        public array $methods = [],
    ) {
    }

    /**
     * 创建路由类实例
     */
    public function newInstance(): object
    {
        return new $this->name();
    }

    /**
     * 添加路由方法
     */
    public function appendMethod(RouteClassMethod $method): void
    {
        $this->methods[] = $method;
    }
}
