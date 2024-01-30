<?php

namespace Library\Attribute\Console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Action
{
    /**
     * @param string $action    命令别名
     * @param string $schedule  如果有值：定时任务时间规则
     * @param bool   $coroutine 是否在协程中运行
     * @param string $desc      命令描述
     * @param string $pre       命令执行前执行的方法(如果存在此方法执行返回 true 才会继续执行命令)
    * @param string $replicas  命令执行的副本数(生成对应的 deployment 副本数量,空则不生成)
     */
    public function __construct(
        public string $action,
        public string $schedule = '',
        public bool $coroutine = true,
        public string $desc = '',
        public string $pre = 'init',
        public string $replicas = '',
    ) {
    }
}
