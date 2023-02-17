<?php

namespace Library\Logger;

use Library\Database\Redis;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class RedisPubSubHandler extends AbstractProcessingHandler
{
    protected Sender $sender;

    /**
     * @param string $connection The redis instance
     * @param string $key        The channel key to publish records to
     */
    public function __construct(string $connection, string $key, int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        $this->sender = new Sender($connection, $key);

        parent::__construct($level, $bubble);
    }

    /**
     * @inheritDoc
     */
    protected function write(LogRecord $record): void
    {
        $this->sender->publish($record->formatted);
    }
}

class Sender extends Redis
{
    protected bool $working = false;

    public function __construct(
        protected string $connection,
        protected string $channelKey,
    ) {
        parent::__construct();
    }

    public function publish($content)
    {
        if ($this->working) {
            return;
        }

        try {
            /** @var \Library\Database\Manager $manager */
            $manager = app('db.pool.redis');

            /** @var ConnectionPool $pool */
            $pool = $manager->pool($this->connection);

            /** @var \Redis $redis */
            $redis = $pool->get();

            $this->working = true;

            $redis->publish($this->channelKey, (string) $content);
        } catch (\Throwable $th) {
        } finally {
            $pool->put($redis);
            $this->working = false;
        }
    }
}
