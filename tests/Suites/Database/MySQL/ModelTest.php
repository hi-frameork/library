<?php

namespace Tests\Unit\Library\Database\MySQL;

use Library\Database\MySQL\QueryProxy;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Tests\Asset\MySQLAsset;

class ModelTest extends MockeryTestCase
{
    /**
     * @var Select|SQLBuilderProxy
     */
    protected $selectQuery;

    protected function setUp(): void
    {
        $this->selectQuery = (new MySQLAsset())->getSelect();
    }

    // 检查 selectQuery 对象
    public function testSelectQueryInstance()
    {
        $this->assertInstanceOf(QueryProxy::class, $this->selectQuery);
    }

    // 测试生成 SQL 与命名绑定值
    public function testSelectBuildSQLWithNameParameters()
    {
        $this->selectQuery->where('bar IN (:ins)', ['ins' => [1, 2, 3]]);

        $sql = <<<EOF
SELECT
    *
FROM
    `test`
WHERE
    bar IN (:__1__, :__2__, :__3__)
EOF;
        $bindValues = [
            '__1__' => 1,
            '__2__' => 2,
            '__3__' => 3,
        ];

        $this->assertSame($sql, $this->selectQuery->getStatement());
        $this->assertSame($bindValues, $this->selectQuery->getBindValues());
    }

    // 测试数据插入
    public function testInsertWithDatabaseAndGetLastId()
    {
        $query = (new MySQLAsset())->getInsert();
        $query->cols([
            'id'      => 1,
            'int1'    => 123,
            'float'   => 1.2,
            'conv_id' => uniqid(),
        ]);
        $query->addRow();
        $query->cols([
            'id'      => 2,
            'int1'    => 123,
            'float'   => 1.2,
            'conv_id' => uniqid(),
        ]);

        $this->assertSame('2', $query->executeAndGetlastId());
    }

    // 测试真实数据库查询（验证 SQL 与参数）
    public function testSelectWithDatabase()
    {
        $result = $this->selectQuery->where(
            'id IN (:ids)',
            [
                'ids' => [1, 2, 3]
            ]
        )->execute();

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame(2, $result[1]['id']);
    }

    // 测试更新数据
    public function testUpdateWithDatabase()
    {
        $query = (new MySQLAsset())->getUpdate();
        $query->col('int1', 123456)->where('id = :id', ['id' => 2]);
        $query->execute();

        $result = $this->selectQuery->where('id = :id', ['id' => 2])->first();

        $this->assertSame(123456, $result['int1']);
    }

    // 测试数据删除
    public function testDeleteWithDatabase()
    {
        $query = (new MySQLAsset())->getDelete();
        $query->where(
            'id IN (:ids)',
            ['ids' => [1, 2]]
        )->execute();

        $this->assertCount(0, $this->selectQuery->execute());
    }
}
