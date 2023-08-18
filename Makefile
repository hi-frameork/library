.PHONY: help
help:
	@echo ""
	@echo "使用说明:"
	@sed -n 's/^##//p' ${MAKEFILE_LIST} | sed 's/^/ /'
	@echo ""

## start   启动本地服务并监听文件变动
.PHONY: start
start: info
	@watchexec -w src -w tests sh tests/make.sh start

## stop    停止服务
.PHONY: stop
stop: info
	@sh tests/make.sh stop

## tests   运行单元测试
.PHONY: tests
tests: info
	@sh tests/make.sh tests

## cs      执行代码优化
.PHONY: cs
cs: info
	@echo '# Code style format'
	@sh tests/make.sh cs

## check   执行编码检查
.PHONY: check
check: info
	@echo '# Code check'
	@sh tests/make.sh check

## shell   进入当前服务容器内
.PHONY: shell
shell: info
	@sh tests/make.sh shell

# 打印环境信息
info:
	@echo '- basedir:' $(shell pwd)
	@echo '- os:     ' $(shell uname | awk '{print tolower($$0)}')
	@echo '- arch:   ' $(shell uname -m)
	@echo ""
