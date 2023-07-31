<?php

namespace Tests\Corontine;

use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
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
        CoroutineStub::create(function () {
            $this->assertNotSame(2, CoroutineStub::getPcid());
        });
    }

    // 在没有 attach 数据的情况下，所有的协程根节点指向首个协程 ID
    public function testGetRpcid()
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
            CoroutineStub::create(function () {
                $this->assertSame(1, CoroutineStub::getRpcid());
                CoroutineStub::create(function () {
                    $this->assertSame(1, CoroutineStub::getRpcid());
                });
            });
        });
    }

    public function testGetPrcideWithDefault()
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
                });
            });
        });
    }

    // 没有执行数据挂载，所以引用计数为 0
    public function testSetReferenceCountWithNoAttach()
    {
        CoroutineStub::create(function () {
            $cid = CoroutineStub::getCid();
            $this->assertSame(0, CoroutineStub::getReferenceCount($cid));
            CoroutineStub::create(function () {
                $cid = CoroutineStub::getCid();
                $this->assertSame(0, CoroutineStub::getReferenceCount($cid));
                CoroutineStub::create(function () {
                    $cid = CoroutineStub::getCid();
                    $this->assertSame(0, CoroutineStub::getReferenceCount($cid));
                });
            });
        });

        CoroutineStub::create(function () {
            $cid = CoroutineStub::getCid();
            $this->assertSame(0, CoroutineStub::getReferenceCount($cid));
            CoroutineStub::create(function () {
                $cid = CoroutineStub::getCid();
                $this->assertSame(0, CoroutineStub::getReferenceCount($cid));
                CoroutineStub::create(function () {
                    $cid = CoroutineStub::getCid();
                    $this->assertSame(0, CoroutineStub::getReferenceCount($cid));
                });
            });
        });
    }

    public function testSetReferenceCountWithSigleLevelAttach()
    {
        $data = ['foo1' => 'bar1'];
        CoroutineStub::attch($data);
        $this->assertSame(1, CoroutineStub::getRpcid());
        // $this->assertSame([1 => $data], CoroutineStub::getAttaches());
        $this->assertSame(1, CoroutineStub::getReferenceCount(1));
        $this->assertSame($data, CoroutineStub::getCtx());
        // $this->assertSame([1 => 1], CoroutineStub::getAllReferenceCount());

        // 同一个协程，引用计数不变，后面数据覆盖前面数据
        $data = ['foo2' => 'bar2'];
        CoroutineStub::attch($data);
        $this->assertSame(1, CoroutineStub::getRpcid());
        // $this->assertSame([1 => $data], CoroutineStub::getAttaches());
        $this->assertSame(1, CoroutineStub::getReferenceCount(1));
        $this->assertSame($data, CoroutineStub::getCtx());
        // $this->assertSame([1 => 1], CoroutineStub::getAllReferenceCount());
    }

    public function testSetReferenceCountWithMultiLevelAttach()
    {
        $data = ['foo1' => 'bar1'];
        CoroutineStub::attch($data);
        $this->assertSame(1, CoroutineStub::getRpcid());
        // $this->assertSame([1 => $data], CoroutineStub::getAttaches());
        $this->assertSame(1, CoroutineStub::getReferenceCount(1));
        // $this->assertSame([1 => 1], CoroutineStub::getAllReferenceCount());
        $this->assertSame($data, CoroutineStub::getCtx());

        // 同一个协程，引用计数不变，后面数据覆盖前面数据
        CoroutineStub::create(function () use ($data) {
            // 在没有 attach 数据的情况下，协程根节点指向首个协程 ID
            $this->assertSame(1, CoroutineStub::getRpcid());

            $cid = CoroutineStub::getCid();
            $data2 = ['foo2' => 'bar2'];
            CoroutineStub::attch($data2);
            $this->assertSame($cid, CoroutineStub::getRpcid());
            $this->assertSame(1, CoroutineStub::getReferenceCount(1));
            $this->assertSame(1, CoroutineStub::getReferenceCount($cid));
            $this->assertSame($data2, CoroutineStub::getCtx());
            // $this->assertSame(
            //     [
            //         1 => 1,
            //         $cid => 1,
            //     ],
            //     CoroutineStub::getAllReferenceCount()
            // );
            // $this->assertSame(
            //     [
            //         1 => $data,
            //         $cid => $data2
            //     ],
            //     CoroutineStub::getAttaches()
            // );
        });
    }
}

class CoroutineStub extends \Library\Coroutine
{
    public static function getRpcid()
    {
        return parent::getRpcid();
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
