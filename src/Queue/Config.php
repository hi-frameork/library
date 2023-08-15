<?php

namespace Library\Queue;

use RuntimeException;

/**
 * 消息队列配置
 */
class Config
{
    /**
     * @var KafkaItem[]
     */
    protected array $list = [];

    public function __construct(array $data)
    {
        foreach ($data as $name => $item) {
            $this->list[$name] = match ($item['type'] ?? '') {
                default => new KafkaItem($item),
            };
        }
    }

    /**
     * 获取指定名称的配置
     *
     * @return KafkaItem
     */
    public function get(string $name)
    {
        if (!isset($this->list[$name])) {
            throw new RuntimeException("Queue config {$name} not found");
        }

        return $this->list[$name];
    }

    /**
     * @return <string, KafkaItem>
     */
    public function getList(): array
    {
        return $this->list;
    }
}

/**
 * 队列配置项
 */
class KafkaItem
{
    /**
     * bootstrapServers 配置
     */
    public string $bootstrapServers;

    /**
     * brokers 配置
     */
    public string $brokers;

    public function __construct(array $data)
    {
        if (!isset($data['bootstrapServers'])) {
            throw new RuntimeException('Kafaka config bootstrapServers not found');
        }

        if (!isset($data['brokers'])) {
            throw new RuntimeException('Kafka config brokers not found');
        }

        $this->bootstrapServers = $data['bootstrapServers'];
        $this->brokers          = $data['brokers'];
    }
}
