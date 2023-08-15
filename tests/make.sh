#!/bin/env sh

# 单元测试运行镜像
Image=public.ecr.aws/o0r2l9b2/typing/php:swoole-8.1-arm64
# Image=xdebug
WorkDir=/var/www

echo '> 正在启动:' $(date '+%Y-%m-%d %H:%M:%S')

case $1 in
  watch)
    # 容器名称
    Name=$(basename `pwd`)-$(echo `pwd` | md5 | cut -c1-10)
    # 目录挂载
    Mount="-v `pwd`/tests/php.ini:/usr/local/etc/php/conf.d/php.ini -v `pwd`:${WorkDir}"
    # 启动容器并进入容器
    docker stop ${Name} >> /dev/null
    docker rm ${Name} >> /dev/null
    docker run --name ${Name} ${Mount} -d ${Image} php -S 0.0.0.0:80
    docker exec -it ${Name} sh
    # apk add util-linux make
  ;;
  tests)
    php ${WorkDir}/tests/start.php --config=${WorkDir}/phpunit.xml
  ;;
  cs)
    php ${WorkDir}/vendor/bin/php-cs-fixer fix --config=${WorkDir}/.php-cs-fixer.dist.php
  ;;
  check)
    php ${WorkDir}/vendor/bin/psalm --alter --issues=MissingReturnType --issues=MissingParamType --dry-run
  ;;
esac
