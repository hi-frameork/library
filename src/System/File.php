<?php

namespace Library\System;

use Nette\Loaders\RobotLoader;
use Nette\Utils\FileSystem;

class File
{
    /**
     * 扫描并返回指定路径下所有类(类不会自动加载)
     *
     * @return array<string> 类名称
     */
    public static function scanDirectoryClass(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $classLoader = new RobotLoader();
        $classLoader->addDirectory($path)->rebuild();

        return $classLoader->getIndexedClasses();
    }

    /**
     * 删除目录
     */
    public static function rmdir($dir)
    {
        return FileSystem::delete($dir);
    }
}
