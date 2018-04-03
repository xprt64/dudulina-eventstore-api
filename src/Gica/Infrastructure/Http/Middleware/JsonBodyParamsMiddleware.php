<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Infrastructure\Http\Middleware;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Enriches the $request with a parsed body (as an array) if the request is of type 'application/json'
 */
class JsonBodyParamsMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($request->hasHeader('Content-type') && false !== stripos($request->getHeader('Content-type')[0], 'json')) {
            $rawBody = (clone $request->getBody())->getContents();

            if (is_string($rawBody)) {
                $request = $request->withParsedBody(json_decode($rawBody, true));
            }
        }

        return $delegate->process($request);
    }
}