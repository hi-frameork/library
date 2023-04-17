<?php

namespace Tests\Unit\Library\Database\MySQL;

use Aura\SqlQuery\QueryFactory;
use Library\Database\MySQL\QueryProxy;
use PHPUnit\Framework\TestCase;

class QueryProxyTest extends TestCase
{
    // 测试 query 对象
    public function testGetQuery()
    {
        $query = (new QueryFactory('mysql'))->newSelect();
        $proxy = new QueryProxy('default', $query);

        $this->assertSame($query, $proxy->getQuery());
    }
}
