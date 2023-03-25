<?php

namespace Library\Http\Response;

use Hi\Http\Message\Response;

/**
 * 文本响应
 */
class TextResponse extends Response
{
    /**
     * 构造函数
     */
    public function __construct(int $status = 200, $body, string $traceId = '')
    {
        parent::__construct($status, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Trace-Id'     => $traceId,
        ]);

        $this->getBody()->write((string) $body);
    }
}
