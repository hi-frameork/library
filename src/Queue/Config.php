<?php

namespace Library\Queue;

use Exception;

class Config
{
    /**
     * @var Item[]
     */
    protected array $list;

    public function __construct(array $data)
    {
        foreach ($data as $name => $item) {
            $this->list[$name] = new Item($item);
        }
    }

    /**
     * 获取指定名称的配置
     */
    public function get(string $name): Item
    {
        if (!isset($this->list[$name])) {
            throw new Exception("Queue config {$name} not found");
        }

        return $this->list[$name];
    }

    /**
     * @return <string, Item>
     */
    public function getList()
    {
        return $this->list;
    }
}

/**
 * 队列配置项
 */
class Item
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
            throw new Exception('bootstrapServers not found');
        }

        if (!isset($data['brokers'])) {
            throw new Exception('brokers not found');
        }

        $this->bootstrapServers = $data['bootstrapServers'];
        $this->brokers          = $data['brokers'];
    }
}