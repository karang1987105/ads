<?php

namespace App\Services;

use Exception;
use Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class RPCClient {
    private $url;
    private $id;

    public function __construct(string $url) {
        $this->url = $url;
        $this->id = 1;
    }

    /**
     * @throws Exception
     */
    public function __call($method, $params) {
        $currentId = $this->id;
        $data = ['jsonrpc' => '2.0', 'method' => $method, 'params' => $params, 'id' => $currentId,];
        $request = Http::withHeaders(config('ads.daemon.connection.headers'))->asJson();
        if (config()->has('ads.daemon.connection.timeout')) {
            $request->timeout(config('ads.daemon.connection.timeout'));
        }
        $respond = $request->post($this->url, $data)
            ->throw(function (Response $response, RequestException $exception) {
                throw new Exception($this->url . ': ' . $exception->getMessage(), $exception->getCode(), $exception);
            })
            ->json();
        if (isset($respond['id']) && $respond['id'] != $currentId) {
            throw new Exception(sprintf('Incorrect response id (request id: %s, response id: %s)', $currentId, $respond['id']), $this->url);
        }
        if (isset($respond['error']) && !is_null($respond['error'])) {
            if (is_string($respond['error'])) { // Compatible with old version
                $respond['error'] = ['message' => $respond['error'], 'code' => 0];
            }
            $message = $respond['error']['message'] ?? 'Unknown error';
            $code = (int)$respond['error']['code'] ?? 0;
            throw new Exception($message, $this->url, $code);
        }
        if (!isset($respond['result'])) {
            throw new Exception('Error response[result]', $this->url);
        }
        return $respond['result'];
    }
}