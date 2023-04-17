.PHONY: help
## help: 打印帮助信息
help:
	@echo "使用说明:"
	@sed -n 's/^##//p' ${MAKEFILE_LIST} | column -t -s ':' |  sed 's/^/ /'

## dev: 本地开发启动服务并监听文件变动
.PHONY: dev
dev: info
	@watchexec -w src -w tests sh tests/make.sh watch

## tests: 单元测试
.PHONY: tests
tests: info
	@watchexec -w src -w tests sh tests/make.sh tests

## cs: 代码优化
.PHONY: cs
cs: info
	@echo '> Code style format'
	@sh tests/make.sh cs

## check: 编码检查
.PHONY: check
check: info
	@echo '> Code check'
	@sh tests/make.sh check

## custom: 自定义命令，例如 composer
.PHONY: custom
custom: info
	@sh tests/make.sh custom

## logs: 打印服务运行的实时日志
.PHONY: logs
logs: info
	@sh tests/make.sh logs

## init: 复制配置文件模版
.PHONY: init
init: info
	@echo 'init 复制配置文件模版:'
	cp ./.deploy/local/application.yaml src/application.yaml

# 打印环境信息
info:
	@echo "> 环境信息"
	@echo 'basedir:' $(shell pwd)
	@echo 'os:     ' $(shell uname | awk '{print tolower($$0)}')
	@echo 'arch:   ' $(shell uname -m)
	@echo ""
