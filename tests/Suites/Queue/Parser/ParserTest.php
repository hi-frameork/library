<?php

namespace Tests\Suites\Queue\Parser;

use Library\Queue\NotFoundException;
use Library\Queue\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    // 测试不存砸需要解析的类
    public function testConstructWithEmpty()
    {
        $this->assertSame(
            [
                'classes' => [],
                'aliases' => [],
            ],
            (new Parser([], ''))->getParsed()
        );
    }

    // 测试空初始化 - 抛出异常
    public function testGetWhenEmptyDataThrowException()
    {
        $name = uniqid();
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Class or Alias '{$name}' not found");

        $parser = new Parser([], '');
        $parser->get($name);
    }
}
