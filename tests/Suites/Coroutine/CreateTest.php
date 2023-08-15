<?php

namespace Tests\Suites\Corontine;

use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    protected function tearDown(): void
    {
        CoroutineStub::clear();
    }

    public function testGetCid()
    {
        $this->assertSame(1, CoroutineStub::getCid());
    }

    public function testGetPid()
    {
        $this->assertSame(-1, CoroutineStub::getPcid());
    }

    public function testGetPidInSubCoroutine()
    {
        CoroutineStub::create(function () {
            $this->assertSame(1, CoroutineStub::getPcid());
        });
        //  预期不等，根携程 ID 为 1
        CoroutineStub::create(function () {
            $this->assertNotSame(2, CoroutineStub::getPcid());
        });
    }

    // 在没有 attach 数据的情况下，所有的协程根节点指向首个协程 ID
    public function testGetRpcidWithSigle()
    {
        $this->assertSame(1, CoroutineStub::getRpcid());

        CoroutineStub::create(function () {
            $this->assertSame(1, CoroutineStub::getRpcid());
        });
        CoroutineStub::create(function () {
            $this->assertSame(1, CoroutineStub::getRpcid());
        });
        CoroutineStub::create(function () {
            $this->assertSame(1, CoroutineStub::getRpcid());
        });
    }

    public function testGetRpcidWithMultiNest()
    {
        CoroutineStub::create(function () {
            $this->assertSame(1, CoroutineStub::getRpcid());
            CoroutineStub::create(function () {
                $this->assertSame(1, CoroutineStub::getRpcid());
                CoroutineStub::create(function () {
                    $this->assertSame(1, CoroutineStub::getRpcid());
                });
            });
        });

        CoroutineStub::create(function () {
            $this->assertSame(1, CoroutineStub::getRpcid());
            CoroutineStub::create(function () {
                $this->assertSame(1, CoroutineStub::getRpcid());
                CoroutineStub::create(function () {
                    $this->assertSame(1, CoroutineStub::getRpcid());
                    CoroutineStub::create(function () {
                        $this->assertSame(1, CoroutineStub::getRpcid());
                        CoroutineStub::create(function () {
                            $this->assertSame(1, CoroutineStub::getRpcid());
                            CoroutineStub::create(function () {
                                $this->assertSame(1, CoroutineStub::getRpcid());
                            });
                        });
                    });
                });
            });
        });
    }

    // 在根节点挂载了 attach 数据的情况下，数据体为挂载的数据
    public function testGetAttachDataWithSingleAttach()
    {
        // 根携程未挂载数据，数据为 null
        CoroutineStub::create(function () {
            $this->assertSame(null, CoroutineStub::getAttachData());
        });

        // 根携程挂载数据
        $data = ['foo' => 'bar'];
        CoroutineStub::attch($data);
        $this->assertSame($data, CoroutineStub::getAttachData());

        // 子协程默认继承父协程的挂载数据
        CoroutineStub::create(function () use ($data) {
            $this->assertSame($data, CoroutineStub::getAttachData());
        });
    }

    // 在根节点没有 attach 数据的情况下，数据体为 null
    public function testGetAttachDataWithNoAttach()
    {
        $this->assertSame(null, CoroutineStub::getAttachData());
    }

    // 嵌套协程挂载情况
    public function testGetAttachDataWithNestAttach()
    {
        CoroutineStub::create(function () {
            // 还未挂载数据，数据为 null
            $this->assertSame(null, CoroutineStub::getAttachData());

            // 根携程挂载数据
            $data = ['foo1' => 'bar1'];
            CoroutineStub::attch($data);

            CoroutineStub::create(function () use ($data) {
                $this->assertSame($data, CoroutineStub::getAttachData());
                CoroutineStub::create(function () use ($data) {
                    $this->assertSame($data, CoroutineStub::getAttachData());
                    CoroutineStub::create(function () use ($data) {
                        $this->assertSame($data, CoroutineStub::getAttachData());
                        CoroutineStub::create(function () use ($data) {
                            $this->assertSame($data, CoroutineStub::getAttachData());
                        });
                    });
                });

                CoroutineStub::create(function () {
                    $data = ['foo2' => 'bar2'];
                    CoroutineStub::attch($data);
                    $this->assertSame($data, CoroutineStub::getAttachData());
                });

                CoroutineStub::create(function () {
                    $data = ['foo3' => 'bar3'];
                    CoroutineStub::attch($data);
                    $this->assertSame($data, CoroutineStub::getAttachData());
                });
            });

            CoroutineStub::create(function () {
                $data = ['foo4' => 'bar4'];
                CoroutineStub::attch($data);
                $this->assertSame($data, CoroutineStub::getAttachData());
            });
        });
    }
}

class CoroutineStub extends \Library\Coroutine
{
    public static function getRpcid()
    {
        return parent::getRpcid();
    }

    public static function clear()
    {
        $context = parent::getContext();
        unset($context->data, $context->reference);
    }

    // public static function getAttaches()
    // {
    //     return static::$attches;
    // }

    // public static function getAllReferenceCount()
    // {
    //     return static::$referenceCount;
    // }
}
