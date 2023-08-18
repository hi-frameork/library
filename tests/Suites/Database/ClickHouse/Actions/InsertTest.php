<?php

namespace Tests\Suites\Database\ClickHouse\Actions;

use ClickHouseDB\Client;
use Tests\Suites\Database\ClickHouse\TestCase;

class InsertTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 创建测试表
        $model = new Stubs\Model();
        $model->doBuiltIn(function (Client $client) {
            $client->write('DROP TABLE IF EXISTS `test`');
            $client->write('CREATE TABLE `test` (id Int32, name String) ENGINE = Memory');
        });
    }

    public function testSelect()
    {
        $data = [
            'id'   => 1,
            'name' => uniqid(),
        ];
        $model  = new Stubs\Model();
        $result = $model->doInsert()->cols($data)->execute();
        print_r($result);
        $result = $model->doSelect()->execute()->rows();
        $this->assertSame([$data], $result);
    }
}
