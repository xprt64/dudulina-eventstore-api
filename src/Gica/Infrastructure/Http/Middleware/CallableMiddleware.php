<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Transform a callable (function or class) into a middleware
 */
class CallableMiddleware implements MiddlewareInterface
{
    /**
     * @var
     */
    private $method;

    public function __construct(
        $method
    )
    {
        $this->method = $method;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return call_user_func($this->method, $request, $delegate);
    }
}