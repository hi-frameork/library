<?php

namespace Library\Attribute\Queue;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Consumer
{
    /**
     * @param string $topic 队列主题
     * @param string $alias 消费者队列别名，可用于手动执行特定消费者类，使用相同别名视为同一组
     */
    public function __construct(
        public string $topic,
        public string $alias = '',
    ) {
    }
}
