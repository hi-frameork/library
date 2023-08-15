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
 * $data = Coroutine::getAttachData();
 * ```
 */
class Coroutine extends SwooleCoroutine
{
    /**
     * 创建协程
     */
    public static function create(callable $func, ...$params)
    {
        return parent::create(function () use ($func, $params) {
            static::attch(
                parent::getContext(static::getRpcid()) ?? null,
                false
            );
            // 执行回调业务
            call_user_func($func, ...$params);
        });
    }

    /**
     * 返回当前协程父 ID 映射的根协程 ID
     */
    protected static function getRpcid()
    {
        if (false === ($pid = parent::getCid())) {
            throw new RuntimeException('Runtime must be in swoole');
        }

        // 循环向上查找，任何协程被 attach 即视为根协程
        while (true) {
            if (parent::getContext($pid)->attachRoot ?? 0) {
                return $pid;
            }

            // 父协程 ID 小于 0，说明当前协程为根协程
            // 跳出循环
            $id = static::getPcid($pid);
            if ($id <= 0) {
                return $pid;
            }
            $pid = $id;
        }
    }

    /**
     * 为当前携程挂载数据
     * 其将会通过 $maps 与 $referenceCount 在所有子协程中共享
     *
     * @param object|array $data
     */
    public static function attch($data, bool $attachRoot = true)
    {
        if (-1 === ($cid = parent::getCid())) {
            throw new RuntimeException('Runtime must be in swoole');
        }

        // if (null === $data) {
        //     trigger_error('协程挂载目标数据不能为 null', E_USER_ERROR);
        // }

        $context = parent::getContext($cid);
        // 标记当前节点是否为根节点
        $context->attachRoot = $attachRoot;
        // 对于根节点，挂载原始数据并创建自身引用，reference 为业务数据
        // 对于普通节点根节点引用，reference 为根节点 context 引用
        if ($attachRoot) {
            $context->data      = $data;
            $context->reference = $context;
        } else {
            $context->reference = $data;
        }
    }

    /**
     * 获取当前协程所在的根协程上挂载的数据
     */
    public static function getAttachData()
    {
        return parent::getContext(parent::getCid())->reference->data ?? null;
    }
}
