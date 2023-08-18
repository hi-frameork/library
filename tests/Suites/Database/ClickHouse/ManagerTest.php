<?php

namespace Tests\Suites\Database\ClickHouse;

class ManagerTest extends TestCase
{
    public function testCreate()
    {
        $pool = $this->manager->pool('default');
        $this->assertInstanceOf(\ClickHouseDB\Client::class, $pool->get());;
    }
}
