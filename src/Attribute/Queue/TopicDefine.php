<?php

namespace Library\Attribute\Queue;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class TopicDefine
{
    /**
     * @param int $partition         分区数
     * @param int $replicationFactor 副本数
     */
    public function __construct(
        public int $partition,
        public int $replicationFactor = -1,
    ) {
    }
}
