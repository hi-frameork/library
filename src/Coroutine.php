<?php

namespace Library;

use RuntimeException;
use Swoole\Coroutine as SwooleCoroutine;

/**
 * 协程上下文，用于在协程间共享数据
 *
 * 在 swoole 协程基础上扩展了协程上下文，可以在协程间共享数据
 * 即将数据挂载到根协程上，所有子/孙协程都可以通过 getContext 方法获取根协程挂载的数据
 *
 * 使用示例：
 * ```php
 * # 挂在数据至当前协程所在的根协程上
 * # 方法内部会根据自身的协程 ID 向上遍历，直到找到根协程 ID
 * # 然后将数据挂载至根协程上
 * Coroutine::attch($data);
 *
 * # 获取当前协程所在的根协程上挂载的数据
 * $data = Coroutine::getContext();
 * ```
 */
class Coroutine extends SwooleCoroutine
{
    /**
     * 协程上下文映射
     */
    protected static array $attches = [];

    /**
     * 协程 ID 映射
     */
    protected static array $maps = [];

    /**
     * 协程引用计数
     */
    protected static array $referenceCount = [];

    /**
     * 创建协程
     */
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
     * 为当前携程挂载数据
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

    /**
     * 释放/清理协程上下文
     */
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

    /**
     * 获取当前协程所在的根协程上挂载的数据
     */
    public static function getContext($uid = null)
    {
        if (!$uid) {
            $uid = static::getuid();
        }

        $rid = static::getRcid($uid);

        return static::$attches[$rid] ?? null;
    }
}
