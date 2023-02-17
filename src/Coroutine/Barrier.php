<?php

namespace Library\Coroutine;

use Swoole\Coroutine\Barrier as CoroutineBarrier;

/**
 * @method static Barrier make()
 * @method static Barrier wait()
 */
class Barrier extends CoroutineBarrier
{
}
