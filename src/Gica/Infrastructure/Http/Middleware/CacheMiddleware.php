<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\Lib\CacheHeadersLib;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;

/**
 * Cache middleware for GET requests
 * If an subsequent middleware returns a response with the Last-modified header and the client
 * supports caching and the cache is hit then a 304 response is sent instead of the full 200 response.
 * This cache improves only the bandwidth, not the server side processing.
 */
class CacheMiddleware implements MiddlewareInterface
{
    /**
     * @var CacheHeadersLib
     */
    private $cacheHeadersLib;

    public function __construct(
        CacheHeadersLib $cacheHeadersLib
    )
    {
        $this->cacheHeadersLib = $cacheHeadersLib;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->process($request);

        if ('GET' === $request->getMethod()) {
            if ($response->hasHeader('Last-modified')) {
                $serverLastModified = new \DateTimeImmutable($response->getHeader('Last-modified')[0]);

                if ($this->cacheHeadersLib->checkIfModifiedSince($request, $serverLastModified) ||
                    $this->cacheHeadersLib->checkIfNoneMatchHeader($request, $serverLastModified)
                ) {
                    $response = $this->emptyBody($response)
                        ->withAddedHeader('Last-modified', $this->cacheHeadersLib->formatClientDate($serverLastModified))
                        ->withAddedHeader('ETag', $this->cacheHeadersLib->factoryEtagFromDateForUrl($request->getUri()->__toString(), $serverLastModified));
                }
            }
        }

        return $response;
    }

    private function emptyBody(ResponseInterface $response): ResponseInterface
    {
        return $response->withBody(new Stream('php://temp', 'r'))->withStatus(304);
    }
}