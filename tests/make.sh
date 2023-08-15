#!/bin/env sh

# 容器名称
Name=$(basename `pwd`)-$(echo `pwd` | md5 | cut -c1-10)
# 单元测试运行镜像
Image=public.ecr.aws/o0r2l9b2/typing/php:swoole-8.1-arm64
# Image=xdebug
WorkDir=/var/www
# 目录挂载
Mount="-v `pwd`/tests/php.ini:/usr/local/etc/php/conf.d/php.ini -v `pwd`:${WorkDir}"
# 服务端口
Port=9527
# 日志路径
LogFile=storage/logs/hi.log

echo '> 正在启动:' $(date '+%Y-%m-%d %H:%M:%S')

case $1 in
  watch)
    echo '+ 访问地址: http://0.0.0.0:'${Port}
    Args="--name ${Name} ${Mount} -d -p ${Port}:80 ${Image} php -S 0.0.0.0:80"
    docker stop ${Name} >> /dev/null
    docker rm ${Name} >> /dev/null
    docker run ${Args}
  ;;
  tests)
    Args="--name ${Name} ${Mount} ${Image} php ${WorkDir}/tests/start.php --config=${WorkDir}/phpunit.xml"
  ;;
  cs)
    Args="--name ${Name} ${Mount} ${Image} php ${WorkDir}/vendor/bin/php-cs-fixer fix --config=${WorkDir}/.php-cs-fixer.dist.php"
  ;;
  check)
    Args="--name ${Name} ${Mount} ${Image} php ${WorkDir}/vendor/bin/psalm --alter --issues=MissingReturnType --issues=MissingParamType --dry-run"
  ;;
esac
