<?php

use Library\Queue\AbstractProducer;

/**
 * 生产者投递消息
 */
function produce(AbstractProducer|string $producer, ?array $data = null)
{
    return app('queue')->produce($producer, $data);
}
