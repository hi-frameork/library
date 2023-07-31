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
 * $data = Coroutine::getCtx();
 * ```
 */
class Coroutine extends SwooleCoroutine
{
    // /**
    //  * 协程上下文映射
    //  */
    // protected static array $attches = [];

    // /**
    //  * 协程引用计数
    //  */
    // protected static array $referenceCount = [];

    /**
     * 创建协程
     */
    public static function create(callable $func, ...$params)
    {
        return parent::create(function () use ($func, $params) {
            // 增加根协程引用计数
            // 如果当前协程执行 attach，根协程需要减一
            static::setReferenceCount(static::getCid(), false);
            // 协程退出，清空关系映射
            // Coroutine::defer(fn () => static::doDefer());
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
        if (-1 === ($cid = static::getCid())) {
            throw new RuntimeException('Runtime must be in swoole');
        }

        // 以当前协程 ID 作为根协程初始化引用计数
        $pid = static::setReferenceCount($cid, true);
        // 协程 ID 绑定数据
        static::setCtx($pid, $data);
        // 协程退出时执行清理
        // Coroutine::defer(fn () => static::doDefer());
    }

    /**
     * 返回当前协程父 ID 映射的根协程 ID
     */
    protected static function getRpcid()
    {
        if (false === ($pid = static::getCid())) {
            throw new RuntimeException('Runtime must be in swoole');
        }

        // 循环向上查找，任何协程被 attach 即视为根协程
        while (true) {
            if (static::getContext($pid)['referenceCount'] ?? 0) {
                return $pid;
            }

            // 父协程 ID 小于 0，说明当前协程为根协程
            $id = static::getPcid($pid);
            if ($id <= 0) {
                return $pid;
            }
            $pid = $id;
        }
    }

    /**
     * 创建协程 ID 映射
     */
    protected static function setReferenceCount($cid, bool $isRoot = false)
    {
        $rpid = static::getRpcid();
        if ($isRoot) {
            // 对于每一个新建的协程，默认会将其父协程的引用计数加一
            // 所以此处需要找到当前协程根协程 ID，将其引用计数减一
            if (static::getContext($rpid)['referenceCount'] ?? 0) {
                parent::getContext($rpid)['referenceCount'] -= 1;
            }

            // 初始化根协程引用计数
            parent::getContext($cid)['referenceCount'] = 1;

            return $cid;
        }

        if (static::getContext($rpid)['referenceCount'] ?? 0) {
            parent::getContext($rpid)['referenceCount'] += 1;
        }

        return $rpid;
    }

    /**
     * 获取指定协程 ID 的引用计数
     */
    public static function getReferenceCount($cid)
    {
        return parent::getContext($cid)['referenceCount'] ?? 0;
    }

    /**
     * 绑定数据
     */
    protected static function setCtx($id, $data)
    {
        parent::getContext($id)['data'] = $data;
    }

    /**
     * 获取指定协程 ID 的上下文
     * 如果未传入协程 ID，则默认获取当前协程指向的根协程上下文
     */
    public static function getCtx($cid = null)
    {
        $id = $cid ?? static::getRpcid();

        return parent::getContext($id)['data'] ?? null;
    }

    /**
     * 释放/清理协程上下文
     */
    // protected static function doDefer()
    // {
    //     $id = static::getRpcid();

    //     // 减少引用计数
    //     if (!empty(static::$referenceCount[$id])) {
    //         static::$referenceCount[$id] -= 1;
    //         // 检查并释放映射资源
    //         if (static::$referenceCount[$id] <= 0) {
    //             unset(static::$referenceCount[$id], static::$attches[$id]);
    //         }
    //     }
    // }
}
