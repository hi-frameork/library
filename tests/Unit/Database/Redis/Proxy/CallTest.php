<?php

namespace Tests\Unit\Database\Redis\Proxy;

class CallTest extends TestCase
{
    public function test_with_subscribe(): void
    {
        $result = $this->redis->subscribe(['test-pub'], function ($redis, $channel, $message) {
        });
        var_dump($result);
    }

    public function test_with_set(): void
    {
        $result = $this->redis->set('test', 'test');
        $this->assertTrue($result);
        $this->redis->del('test');
    }
}
