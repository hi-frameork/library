<?php

namespace Library\Queue;

use Library\System\File;
use ReflectionClass;

class Manager
{
    protected ProducerRunner $producerRunner;

    protected ConsumerRunner $consumerRunner;

    public function __construct(array $configs)
    {
        $this->producerRunner = new ProducerRunner();
        $this->consumerRunner = new ConsumerRunner();
    }

    /**
     * 扫描并加载生产者与消费者定义
     *
     * @param string|string[] $paths 需要扫描的生产者定义的路径
     */
    public function load($paths): self
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        $classes = [];
        foreach ($paths as $path) {
            $classes += File::scanDirectoryClass($path);
        }

        // 解析生产者
        foreach ($classes as $class) {
            if ($producer = $this->parseProducer($class)) {
                $this->producerRunner->add($producer);
            }
        }

        // 解析消费者
        foreach ($classes as $class) {
            $this->parseConsumer($class);
        }

        return $this;
    }

    protected function parseProducer(string $class): ?AbstractProducer
    {
        $reflectionClass = new ReflectionClass($class);
    }

    protected function parseConsumer(string $class)
    {
    }

    public function produce(AbstractProducer $producer)
    {
    }

    /**
     * 启动消费者
     *
     * 支持以下两种启动方式：
     * 1. 未传入参数，启动所有消费者
     * 2. 传入参数，启动指定消费者
     *  - 传入消费者别名，启动指定消费者/组(业务重定义分组与 kafka 消费组不同)
     *  - 传入消费者类名，启动指定消费者
     *
     * @param string|null $aliasOrClassName 消费者别名或类名
     */
    public function consume(?string $aliasOrClassName = null): void
    {
        return $this->consumerRunner->run($aliasOrClassName);
    }
}
