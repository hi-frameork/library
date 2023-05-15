<?php

namespace Library\Queue;

/**
 * 生产者运行器
 */
class ProducerRunner
{
    protected array $producers = [];

    /**
     * 添加生产者
     */
    public function add(AbstractProducer $producer): self
    {
        $this->producers[] = $producer;
        return $this;
    }

    public function run()
    {}
}
