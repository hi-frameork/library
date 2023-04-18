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
     * @param string $alias    中间件别名名称
     * @param int    $priority 中间件优先级，值越小优先级越高
     */
    public function __construct(
        public string $alias = '',
        public int $priority = 0,
    ) {
    }
}
