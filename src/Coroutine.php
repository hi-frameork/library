<?php

namespace Library;

use ArrayAccess;
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
class Coroutine
{
    /**
     * 创建协程
     *
     * @param callable          $fn
     * @param array<int, mixed> $params
     */
    public static function create(callable $fn, ...$params): mixed
    {
        return SwooleCoroutine::create(
            fn ($fn, $params, $data) => Coroutine::setAttach($data) && tryCatch($fn, $params, false),
            $fn,
            $params,
            static::getAttach()
        );
    }

    /**
     * 为当前携程挂载数据
     * 其将会通过 $maps 与 $referenceCount 在所有子协程中共享
     *
     * @deprecated 见 setAttach() 方法
     */
    public static function attch(mixed $data, string $name = '__data'): bool
    {
        return static::setAttach($data, $name);
    }

    /**
     * 为当前携程挂载数据
     * 其将会通过 $maps 与 $referenceCount 在所有子协程中共享
     */
    public static function setAttach(mixed $data, string $name = '__data'): bool
    {
        $context = static::getContext();

        if ($data === null) {
            unset($context[$name]);
        } else {
            $context[$name] = $data;
        }

        return true;
    }

    /**
     * @return mixed
     * @deprecated 见 getAttach() 方法
     */
    public static function getAttachData()
    {
        return static::getAttach();
    }

    /**
     * 获取当前协程所在的根协程上挂载的数据
     */
    public static function getAttach(string $name = '__data'): mixed
    {
        return static::getContext()[$name] ?? null;
    }

    /**
     * @return ArrayAccess
     */
    public static function getContext(): object
    {
        return SwooleCoroutine::getContext();
    }

    /**
     * @param int|float $seconds
     * @return mixed
     */
    public static function sleep(int|float $seconds)
    {
        return SwooleCoroutine::sleep($seconds);
    }

    /**
     * @param mixed $callback
     * @return mixed
     */
    public static function defer(callable $callback)
    {
        return SwooleCoroutine::defer($callback);
    }
}
