<?php

namespace Library\Attribute\Queue;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Producer
{
    /**
     * @param string $topic 队列主题
     */
    public function __construct(
        public string $topic,
    ) {
    }
}
