<?php

namespace Library\Queue;

use Hi\Kernel\Attribute\Reader;
use Library\Attribute\Queue\Producer;
use Library\System\File;
use ReflectionClass;

class Manager
{
    protected ProducerRunner $producerRunner;

    protected ConsumerRunner $consumerRunner;

    protected array $classes = [];

    public function __construct(array $configs)
    {
        // $this->producerRunner = new ProducerRunner();
        // $this->consumerRunner = new ConsumerRunner();
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

        foreach ($paths as $path) {
            $this->classes = array_merge($this->classes, File::scanDirectoryClass($path));
        }

        return $this;
    }

    /**
     * 为命令行增加手动投递消息的能力
     */
    public function producer()
    {
        $runner = new ProducerRunner();

        // 解析生产者
        foreach ($this->classes as $class) {
            if ($producer = $this->parseProducer($class)) {
                $this->producerRunner->add($producer);
            }
        }
    }

    protected function parseProducer(string $class): ?AbstractProducer
    {
        $reflectionClass = new ReflectionClass($class);
        // 如果类没有注解，代表非生产者类
        $attribute = Reader::getClassAttribute($reflectionClass, Producer::class);
        if (!$attribute) {
            return null;
        }
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
    public function consumer(?string $aliasOrClassName = null): void
    {
        $runner = new ConsumerRunner();

        // 解析消费者
        foreach ($this->classes as $class) {
            $this->parseConsumer($class);
        }

        // return $runner->run($aliasOrClassName);
    }

    protected function parseConsumer(string $class)
    {
    }
}
