<?php

namespace Library;

use RuntimeException;
use Swoole\Coroutine as SwooleCoroutine;

class Coroutine extends SwooleCoroutine
{
    protected static array $attches = [];

    protected static array $maps = [];

    protected static array $referenceCount = [];

    public static function create(callable $func, ...$params)
    {
        return parent::create(function () use ($func, $params) {
            static::setMaps(static::getuid());
            // 协程退出，清空关系映射
            Coroutine::defer([static::class, 'doDefer']);
            // 执行回调业务
            call_user_func($func, ...$params);
        });
    }

    /**
     * 为当前携程挂载 $data
     * 其将会通过 $maps 与 $referenceCount 在所有子协程中共享
     */
    public static function attch($data)
    {
        if (-1 === ($uid = static::getuid())) {
            throw new RuntimeException('Runtime must be in swoole');
        }

        static::$attches[$uid]        = $data;
        static::$referenceCount[$uid] = 0;

        static::setMaps(static::getuid());

        Coroutine::defer([static::class, 'doDefer']);
    }

    public static function doDefer()
    {
        $uid = static::getuid();
        $rid = static::getRcid($uid);

        // 减少引用计数
        // 检查并释放映射资源
        if (!empty(static::$referenceCount[$rid])) {
            static::$referenceCount[$rid] -= 1;

            if (static::$referenceCount[$rid] <= 0) {
                unset(static::$referenceCount[$rid], static::$attches[$rid]);
            }
        }

        unset(static::$maps[$uid]);
    }

    /**
     * 返回当前协程父 ID 映射的根协程 ID
     */
    public static function getPrcid()
    {
        if (false === ($pid = static::getPcid())) {
            throw new RuntimeException('Runtime must be in swoole');
        }

        return static::$maps[$pid] ?? static::getuid();
    }

    /**
     * 返回指定 id 映射的根协程 ID
     */
    public static function getRcid(int $uid)
    {
        return static::$maps[$uid] ?? $uid;
    }

    /**
     * 创建协程 ID 映射
     */
    protected static function setMaps($uid)
    {
        $rid                = static::getPrcid();
        static::$maps[$uid] = $rid;

        // 引用增加计数
        // 用以确保上线文可以被安全释放
        if (isset(static::$referenceCount[$rid])) {
            static::$referenceCount[$rid] += 1;
        }
    }

    public static function getContext($uid = null)
    {
        if (!$uid) {
            $uid = static::getuid();
        }

        $rid = static::getRcid($uid);

        return static::$attches[$rid] ?? null;
    }
}
