<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Infrastructure\Http\Middleware;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Enriches the $request with a parsed body (as an array) if the request is of type 'application/x-www-form-urlencoded'
 */
class FormBodyParamsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($request->hasHeader('Content-type') && 0 === stripos($request->getHeader('Content-type')[0], 'application/x-www-form-urlencoded')) {
            $rawBody = (clone $request->getBody())->getContents();

            if (is_string($rawBody)) {
                parse_str($rawBody, $parsedBody);

                $request = $request->withParsedBody($parsedBody);
            }
        }

        return $delegate->process($request);
    }
}