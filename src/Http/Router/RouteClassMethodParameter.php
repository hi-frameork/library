<?php

namespace Library\Http\Router;

/**
 * 路由类方法参数
 */
class RouteClassMethodParameter
{
    /**
     * @param bool                 $injectable  作为方法的参数是否为可注入对象
     * @param bool                 $isClassType 是否为 class 类型（自动实例化）
     * @param InputClassProperty[] $properties
     */
    public function __construct(
        public string $name,
        public string $type,
        public bool $injectable = false,
        public bool $isClassType = false,
        public array $properties = [],
    ) {
    }

    /**
     * 创建路由类方法参数实例
     */
    public function newInstance(): object
    {
        return new $this->type();
    }

    /**
     * 添加路由类方法参数属性
     */
    public function appendProperty(InputClassProperty $property): void
    {
        $this->properties[] = $property;
    }
}
