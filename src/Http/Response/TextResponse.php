<?php

namespace Library\Http\Response;

use Hi\Http\Message\Response;

class TextResponse extends Response
{
    public function __construct(int $status = 200, $body, string $traceId = '')
    {
        parent::__construct($status, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Trace-Id'     => $traceId,
        ]);

        $this->getBody()->write((string) $body);
    }
}
