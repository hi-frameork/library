<?php

namespace Library\Attribute\Types;

use Attribute;

/**
 * 中间件注解
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Middleware
{
    /**
     * @param string $name 中间件名称
     * @param string $desc 中间件说明
     * @param string $group 中间件分组
     * @param int $priority 中间件优先级
     */
    public function __construct(
        public string $name = '',
        public string $desc = '',
        public string $group = '',
        public int $priority = 0,
    ) {
    }
}