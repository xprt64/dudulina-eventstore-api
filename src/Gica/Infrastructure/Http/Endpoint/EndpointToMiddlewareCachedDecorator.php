<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Infrastructure\Http\Endpoint;


use Gica\Infrastructure\Http\Lib\CacheHeadersLib;
use Gica\Infrastructure\Http\Middleware\ContentNegociationMiddleware;
use Gica\Infrastructure\Http\ParameterInjecterMiddleware\ParameterInjecter;
use Gica\Rest\Cache\CacheableByDateEndpoint;
use Gica\Rest\Endpoint\EndpointResponse;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware that wraps a REST endpoint. It responds with a negotiated HTTP response.
 * It tries to do intelligent caching by asking the compatible (CacheableByDateEndpoint) endpoints about the last modification date
 * and negotiate with the client to send a 304 Not modified response in case of cache hit.
 * Event if the client does not have a copy of the data, if the endpoint is compatible this middleware
 * sends the proper caching headers for future requests (ETag and Last-modified headers)
 */
class EndpointToMiddlewareCachedDecorator implements MiddlewareInterface
{
    /**
     * @var ContentNegociationMiddleware
     */
    private $contentNegociationMiddleware;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string
     */
    private $endpointName;
    /**
     * @var ParameterInjecter
     */
    private $parameterInjecter;
    /**
     * @var CacheHeadersLib
     */
    private $cacheHeadersLib;

    public function __construct(
        ContentNegociationMiddleware $contentNegociationMiddleware,
        ContainerInterface $container,
        string $endpointName,
        ParameterInjecter $parameterInjecter,
        CacheHeadersLib $cacheHeadersLib
    )
    {
        $this->contentNegociationMiddleware = $contentNegociationMiddleware;
        $this->container = $container;
        $this->endpointName = $endpointName;
        $this->parameterInjecter = $parameterInjecter;
        $this->cacheHeadersLib = $cacheHeadersLib;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var CacheableByDateEndpoint $endpoint */
        $endpoint = $this->container->get($this->endpointName);

        $serverLastModifiedDate = null;
        if ($endpoint instanceof CacheableByDateEndpoint) {
            $serverLastModifiedDate = $endpoint->getLastModifiedDate($request);
        }

        $endpointResponse = null;

        if ($serverLastModifiedDate && $this->shouldCacheResponse($request, $serverLastModifiedDate)) {
            $httpResponse = $this->contentNegociationMiddleware->factoryHttpResponse(null)
                ->withStatus(304);
        } else {
            $httpResponse = $this->contentNegociationMiddleware->factoryHttpResponse(
                $this->parameterInjecter->injectParametersFromRequestAndReturnEndpointResponse($request, $endpoint));
        }

        if ($serverLastModifiedDate) {
            $httpResponse = $httpResponse
                ->withHeader('ETag', $this->cacheHeadersLib->factoryEtagFromDateForUrl($request->getUri()->__toString(), $serverLastModifiedDate))
                ->withHeader('Last-modified', $this->cacheHeadersLib->formatClientDate($serverLastModifiedDate));
        }

        return $httpResponse;
    }

    private function shouldCacheResponse(ServerRequestInterface $request, \DateTimeImmutable $lastModifiedDate): bool
    {
        return $this->cacheHeadersLib->checkIfModifiedSince($request, $lastModifiedDate) ||
            $this->cacheHeadersLib->checkIfNoneMatchHeader($request, $lastModifiedDate);
    }
}