FROM public.ecr.aws/o0r2l9b2/typing/php:swoole-8.1.13-arm64

RUN docker-php-source extract \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
	&& docker-php-source delete