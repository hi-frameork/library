<?php

namespace Tests\Suites\Database\ClickHouse\Actions;

use ClickHouseDB\Client;
use Tests\Suites\Database\ClickHouse\TestCase;

class SelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 创建测试表
        $model = new Stubs\Model();
        $model->doBuiltIn(function (Client $client) {
            $client->write('DROP TABLE IF EXISTS `test`');
            $client->write('CREATE TABLE `test` (id Int32, name String) ENGINE = Memory');
            $client->write('INSERT INTO `test` VALUES (1, \'test\')');
        });
    }

    public function testSelect()
    {
        $model  = new Stubs\Model();
        $result = $model->doSelect()->execute()->rows();
        $this->assertSame([['id' => 1, 'name' => 'test']], $result);
    }
}
