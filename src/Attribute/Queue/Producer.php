<?php

namespace Library\Attribute\Queue;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Producer
{
    /**
     * @param string $alias       生产者组名，使用相同别名视为同一组（用于生产多个不同 topic 消息）
     * @param string $description 生产者描述
     */
    public function __construct(
        public string $alias = '',
        public string $description = '',
    ) {
    }
}
