<?php

namespace Library\Queue;

use Library\Attribute\Queue\Consumer;
use Library\Attribute\Queue\Producer;
use Library\System\File;

class Manager
{
    /**
     * 队列管理器
     */
    protected ProducerRunner $producerRunner;

    /**
     * 外部指定的生产者与消费者类
     */
    protected array $classes = [];

    /**
     * 队列配置管理器
     */
    protected Config $config;

    public function __construct(array $configs, protected array $paths)
    {
        $this->config = new Config($configs);
        $this->load($paths);

        // 初始化生产者运行器
        $parser               = new Parser(array_keys($this->classes), Producer::class);
        $this->producerRunner = new ProducerRunner($this->config, $parser);
    }

    /**
     * 扫描并加载生产者与消费者定义
     *
     * @param string|string[] $paths 需要扫描的生产者定义的路径
     */
    protected function load($paths): self
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
     * 外部指定的生产者与消费者类锁在路径
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * 返回队列配置管理器
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 为命令行增加手动投递消息的能力
     */
    public function produce(AbstractProducer|string $producer, ?array $data = null): void
    {
        $this->producerRunner->run($producer, $data);
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
        // 消费者类解析
        $parser = new Parser(array_keys($this->classes), Consumer::class);

        // 启动消费者
        $runner = new ConsumerRunner($this->config, $parser);
        $runner->run($aliasOrClassName);
    }
}
