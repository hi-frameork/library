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
    # docker build -t xdebug -f tests/EsDebug/Dockerfile .
    echo '+ 访问地址: http://0.0.0.0:'${Port}
    Args="--name ${Name} ${Mount} -d -p ${Port}:80 ${Image} php -S 0.0.0.0:80 -t ${WorkDir}/tests/"
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
  logs)
    touch ${LogFile}
    tail -f ${LogFile} | sed \
      -e 's/\(.*\.EMERGENCY.*\)/\x1B[31m\1\x1B[39m/' \
      -e 's/\(.*\.ALERT.*\)/\x1B[31m\1\x1B[39m/' \
      -e 's/\(.*\.CRITICAL.*\)/\x1B[31m\1\x1B[39m/' \
      -e 's/\(.*\.ERROR.*\)/\x1B[31m\1\x1B[39m/' \
      -e 's/\(.*\.WARNING.*\)/\x1B[35m\1\x1B[39m/' \
      -e 's/\(.*\.NOTICE.*\)/\x1B[36m\1\x1B[39m/' \
      -e 's/\(.*\.INFO.*\)/\x1B[34m\1\x1B[39m/' \
      -e 's/\(.*\.DEBUG.*\)/\x1B[33m\1\x1B[39m/' \
      -e 's/\(message\:\)/\x1B[32m\1\x1B[39m/' \
      -e 's/\(context\:\)/\x1B[32m\1\x1B[39m/' \
      -e 's/\(point:\)/\x1B[32m\1\x1B[39m/' \
      -e 's/\(stack:\)/\x1B[32m\1\x1B[39m/'
  ;;
  custom)
    echo '输入自定义命令:'
    read args
    Args="--name ${Name} ${Mount} ${Image} ${args}"
  ;;
esac

# 创建/重启服务
if [ $1 != 'logs' ]; then
  docker stop ${Name} >> /dev/null
  docker rm ${Name} >> /dev/null
  docker run ${Args}
fi
