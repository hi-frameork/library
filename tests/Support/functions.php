<?php

/**
 * 写入日志
 * @param Level $level
 */
function writeLog($level, $message, array $context = []): void
{
}

/**
 * 紧急类型日志
 */
function emergency(string $message, array $context = []): void
{
}

/**
 * 警戒类型日志
 */
function alert(string $message, array $context = []): void
{
}

/**
 * 严重类型日志
 */
function critical(string $message, array $context = []): void
{
}

/**
 * 错误类型日志
 */
function error(string $message, array $context = []): void
{
}

/**
 * 警告类型日志
 */
function warning(string $message, array $context = []): void
{
}

/**
 * 通知类型日志
 */
function notice(string $message, array $context = []): void
{
}

/**
 * 普通类型日志
 */
function info(string $message, array $context = []): void
{
}

/**
 * 调试类型日志
 */
function debug(string $message, array $context = []): void
{
}

function tryCatch(): mixed
{
}
