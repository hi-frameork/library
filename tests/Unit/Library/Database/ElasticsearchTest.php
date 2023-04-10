<?php

namespace Tests\Unit\Library\Database;

use PHPUnit\Framework\TestCase;
use Tests\Asset\ElasticsearchAsset;

class ElasticsearchTest extends TestCase
{
    protected ElasticsearchAsset $esIndex;

    protected function setUp(): void
    {
        $this->esIndex = new ElasticsearchAsset();
    }

    // 测试获取索引名称
    public function testGetIndex()
    {
        $this->assertSame('phpunit-test-index', $this->esIndex->doGetIndex());
    }

    // 测试 ElasticesIndex Client 对象
    // public function testGetClient()
    // {
    //     $this->assertInstanceOf(\Elasticsearch\Client::class, $this->esIndex->doGetClient());
    // }

    // 测试创建 Index && Doc
    public function testCreateIndex()
    {
        $result = $this->esIndex->doIndex([
            'id'         => '1234567890',
            'created_at' => '2022-10-11 22:41:56',
        ], '1234567890');

        $this->assertSame('1234567890', $result);
    }

    // 测试获取 Doc
    public function testGetDefault()
    {
        $result = $this->esIndex->doGet('1234567890');
        $this->assertSame([
            'id'         => '1234567890',
            'created_at' => '2022-10-11 22:41:56',
        ], $result);
    }

    // 测试记录未找到抛出异常
    public function testGetNotFoundWillThrowException()
    {
        $this->expectException(\Elasticsearch\Common\Exceptions\Missing404Exception::class);
        $this->expectExceptionCode(404);
        $this->esIndex->doGet('__sfk23');
    }

    // 测试记录未找到不抛出异常
    public function testGetNotFoundWillNotThrowException()
    {
        $result = $this->esIndex->doGet('__sfk23', [], false);
        $this->assertNull($result);
    }

    // 测试所有命中情况
    public function testesSearchDefault()
    {
        // ES 处理速度太慢，单测太快，等等 ES
        usleep(900000);
        $result = $this->esIndex->doSearch([
            'query' => [
                'match' => [
                    'id' => '1234567890'
                ]
            ],
        ]);

        $expect = [
            'total' => [
                'value'    => 1,
                'relation' => 'eq',
            ],
            'hits' => [
                [
                    'id'         => '1234567890',
                    'created_at' => '2022-10-11 22:41:56',
                ]
            ]
        ];

        $this->assertSame($expect, $result);
    }

    // 测试搜索未命中情况
    public function testesSearchNotHits()
    {
        $result = $this->esIndex->doSearch([
            'query' => [
                'match' => [
                    'id' => '1234567890a'
                ]
            ]
        ]);

        $expect = [
            'total' => [
                'value'    => 0,
                'relation' => 'eq',
            ],
            'hits' => []
        ];

        $this->assertSame($expect, $result);
    }

    // 测试搜索时索引不存在抛出异常
    public function testesSearchWithIndexNotExistWillThrowException()
    {
        $this->expectException(\Elasticsearch\Common\Exceptions\Missing404Exception::class);
        $this->expectExceptionCode(404);
        (new NotExistIndex())->doSearch([
            'query' => [
                'match' => [
                    'id' => '1234567890'
                ]
            ]
        ]);
    }

    // 测试删除文档
    /** @depends testesSearchDefault */
    public function testDelete()
    {
        $result = $this->esIndex->doDelete([
            'id' => '1234567890',
        ]);

        $this->assertTrue($result);
    }

    // 测试删除索引
    /** @depends testDelete */
    public function testIndicesDeleteIndex(): void
    {
        $this->esIndex->doIndices()->delete([
            'index' => $this->esIndex->doGetIndex(),
        ]);

        $this->expectException(\Elasticsearch\Common\Exceptions\Missing404Exception::class);
        $this->expectExceptionCode(404);

        $this->esIndex->doIndices()->get([
            'index' => $this->esIndex->doGetIndex(),
        ]);
    }

    public function testConnectionNotExists()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Database connection pool 'not-exist' does not exist");
        (new NotExistsConnection())->doGet('1234567890');
    }
}

class NotExistIndex extends ElasticsearchAsset
{
    protected string $index = 'phpunit-not-exist-index';
}

class NotExistsConnection extends ElasticsearchAsset
{
    protected string $connection = 'not-exist';
}
