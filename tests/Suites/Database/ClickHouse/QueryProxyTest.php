<?php

namespace Tests\Suites\Database\ClickHouse;

use Aura\SqlQuery\Common\DeleteInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\QueryFactory;
use ClickHouseDB\Client;
use ClickHouseDB\Statement;
use Library\Database\ClickHouse\QueryProxy;

class QueryProxyTest extends TestCase
{
    public function testConstruct()
    {
        $selectQ = new QueryProxyStub($this->connection, (new QueryFactory('mysql'))->newSelect());
        $this->assertInstanceOf(SelectInterface::class, $selectQ->getQuery());

        $insertQ = new QueryProxyStub($this->connection, (new QueryFactory('mysql'))->newInsert());
        $this->assertInstanceOf(InsertInterface::class, $insertQ->getQuery());

        $updateQ = new QueryProxyStub($this->connection, (new QueryFactory('mysql'))->newUpdate());
        $this->assertInstanceOf(UpdateInterface::class, $updateQ->getQuery());

        $deleteQ = new QueryProxyStub($this->connection, (new QueryFactory('mysql'))->newDelete());
        $this->assertInstanceOf(DeleteInterface::class, $deleteQ->getQuery());
    }

    // 测试代理的方法调用
    //  分配连接池连接
    public function testBuiltIn()
    {
        // $query = new QueryProxyStub($this->connection, (new QueryFactory('mysql'))->newSelect()->cols(['*']));

        //  测试初始化连接池长度是否为 0
        $this->assertSame(0, $this->manager->pool($this->connection)->num());
        $this->assertSame(0, $this->manager->pool($this->connection)->length());

        // 测试回调传入的参数是否为连接池中的连接 \ClickHouseDB\Client
        // $query->doBuiltIn(fn ($client) => $this->assertInstanceOf(Client::class, $client));

        // var_dump($this->manager->pool($this->connection));
        // 测试连接池长度是否为 1
        // 只有一个连接被分配出去
        // $connectCount = $this->manager->pool($this->connection)->length();
        // $this->assertSame(1, $connectCount);

        // 测试连接是否被正常回收
        // $query->doBuiltIn(
        //     fn () => $this->assertSame($connectCount - 1, $this->manager->pool($this->connection)->count())
        // );
        // $this->assertSame($connectCount, $this->manager->pool($this->connection)->length());
        // $this->assertSame(1, $this->manager->pool($this->connection)->length());
    }

    // 测试代理的方法调用
    public function testExecute()
    {
        /** @var SelectInterface|QueryProxyStub $query */
        $query = new QueryProxyStub($this->connection, (new QueryFactory('mysql'))->newSelect()->cols(['*']));
        $stat  = $query->from('abc')->where('a = 1')->execute();
        $this->assertInstanceOf(Statement::class, $stat);
    }
}

class QueryProxyStub extends QueryProxy
{
    public function doBuiltIn(callable $callback)
    {
        return $this->builtIn($callback);
    }
}
