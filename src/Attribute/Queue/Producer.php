<?php

namespace Library\Attribute\Queue;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Producer
{
    /**
     * @param string $topic 队列主题
     * @param string $alias 生产者组名，使用相同别名视为同一组（用于生产多个不同 topic 消息）
     */
    public function __construct(
        public string $topic,
        public string $alias = '',
    ) {
    }
}
