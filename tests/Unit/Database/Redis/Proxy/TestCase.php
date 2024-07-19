<?php

namespace Tests\Unit\Database\Redis\Proxy;

use Library\Database\Redis\Proxy;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    protected \Redis|Proxy $redis;

    protected function setUp(): void
    {
        $this->redis = new Proxy('default');
    }
}
