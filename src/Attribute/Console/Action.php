<?php

namespace Library\Attribute\Console;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Action
{
    /**
     * @param string $action      命令别名
     * @param string $schedule    如果有值：定时任务时间规则
     * @param bool   $coroutine   是否在协程中运行
     * @param string $description 命令描述
     */
    public function __construct(
        public string $action,
        public string $schedule = '',
        public bool $coroutine = true,
        public string $description = '',
    ) {
    }
}
