<?php

namespace Library\Database\Elasticsearch;

use GuzzleHttp\Ring\Core;
use GuzzleHttp\Ring\Exception\RingException;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use Library\Coroutine;
use Library\Http\Client;

/**
 * ES 请求处理器
 *
 * 代码参照 https://github.com/Aquarmini/swoft-elasticsearch/blob/1918f12056104515efb196d3786853a451fcf827/src/CoroutineHandler.php
 */
class RequestHandler
{
    public function __construct(protected array $settings = [])
    {
    }

    /**
     * @param array $request 内容结构
     *                       Array (
     *                       [http_method] => GET
     *                       [scheme] => http
     *                       [uri] => /
     *                       [body] =>
     *                       [headers] => Array (
     *                       [Host] => Array (
     *                       [0] => 192.168.64.2
     *                       )
     *                       [Content-Type] => Array (
     *                       [0] => application/json
     *                       )
     *                       [Accept] => Array (
     *                       [0] => application/json
     *                       )
     *                       [User-Agent] => Array (
     *                       [0] => elasticsearch-php/7.17.0 (Darwin 22.3.0; PHP 8.2.3)
     *                       )
     *                       [x-elastic-client-meta] => Array (
     *                       [0] => es=7.17.0,php=8.2.3,t=7.17.0,a=0,cu=7.88.1
     *                       )
     *                       )
     *                       [client] => Array (
     *                       [curl] => Array (
     *                       [107] => 1
     *                       [10005] => elastic:J2yf8wGK700YH3325QSA5kik
     *                       [3] => 30001
     *                       )
     *                       [x-elastic-client-meta] => 1
     *                       [port_in_header] =>
     *                       )
     *                       )
     */
    public function __invoke(array $request)
    {
        return $this->doRquest($request);
    }

    /**
     * 从给定参数创建协程客户端
     *
     * @param array $params
     */
    private function doRquest(array $request)
    {
        $method = $request['http_method'] ?? 'GET';
        $scheme = $request['scheme']      ?? 'http';
        $ssl    = $scheme === 'https';

        // 解析URL
        $effectiveUrl = Core::url($request);
        $parsed       = parse_url($effectiveUrl);

        // 主机地址
        $host = $parsed['host'] ?? '';
        if (empty($host)) {
            throw new RingException('Host is not set');
        }

        // 端口
        $port = $request['client']['curl'][CURLOPT_PORT] ?? 0;
        if (empty($port)) {
            throw new RingException('Port is not set');
        }

        // 路径
        $path = $parsed['path'] ?? '/';
        if (isset($parsed['query'])  && is_string($parsed['query'])) {
            $path .= '?' . $parsed['query'];
        }

        // 记录开始时间
        $btime = microtime(true);

        // 发起请求
        $client = new Client($host, $port, $ssl);
        $client->setMethod($method);
        $client->setData($request['body'] ?? '');
        $client->setHeaders($this->processHeaders($request, $client));
        $client->set($this->processSettings($this->settings));
        $client->execute($path);

        // 检查请求结果
        $this->checkStatusCode($client->statusCode, $client->errCode, $client->errMsg);

        return new CompletedFutureArray([
            'transfer_stats' => [
                'total_time' => microtime(true) - $btime,
            ],
            'effective_url' => $effectiveUrl,
            'headers'       => isset($client->headers) ? $client->headers : [],
            'status'        => $client->statusCode,
            'body'          => $this->getBodyStream($client->body),
        ]);
    }

    /**
     * 处理请求头
     */
    private function processHeaders(array $request): array
    {
        $headers = [];
        foreach ($request['headers'] ?? [] as $name => $value) {
            $headers[$name] = implode(',', $value);
        }

        $password = $request['client']['curl'][CURLOPT_USERPWD] ?? '';
        if ($password) {
            $headers['Authorization'] = sprintf('Basic %s', base64_encode($password));
        }

        return $headers;
    }

    /**
     * 处理设置
     */
    private function processSettings(array $options): array
    {
        $settings = [];
        if (isset($options['delay'])) {
            Coroutine::sleep((float) $options['delay'] / 1000);
        }

        // 超时
        if (isset($options['timeout']) && $options['timeout'] > 0) {
            $settings['timeout'] = $options['timeout'];
        }

        return $settings;
    }

    /**
     * 检查状态码结果，如果请求失败抛出异常
     */
    private function checkStatusCode($statusCode, $errorCode, $errorMessage)
    {
        if ($statusCode === -1) {
            return new RingException(
                sprintf("Connection timed out errCode=%s errMsg=%s", $errorCode, $errorMessage)
            );
        }
        if ($statusCode === -2) {
            return new RingException('Request timed out');
        }

        return true;
    }

    /**
     * 获取响应体
     */
    protected function getBodyStream(string $resource)
    {
        $stream = fopen('php://temp', 'r+');
        if ($resource !== '') {
            fwrite($stream, $resource);
            fseek($stream, 0);
        }

        return $stream;
    }
}
