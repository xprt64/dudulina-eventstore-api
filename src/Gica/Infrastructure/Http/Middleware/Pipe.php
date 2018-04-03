<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\Middleware\Delegate\CallableDelegate;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A piped middleware. All piped middlewares are called one by one and then it delegates.
 */
class Pipe implements MiddlewareInterface
{
    private $piped = [];
    /**
     * @var Factory
     */
    private $middlewareFactory;

    public function __construct(
        Factory $middlewareFactory
    )
    {
        $this->middlewareFactory = $middlewareFactory;
    }


    public function pipe($middleware)
    {
        $this->piped[] = $middleware;
    }


    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (empty($this->piped)) {
            return $delegate->process($request);
        }

        $firstMiddleware = $this->middlewareFactory->factoryMiddleware(array_shift($this->piped));

        return $firstMiddleware->process($request, new CallableDelegate(function (ServerRequestInterface $request) use ($delegate) {
            return $this->process($request, $delegate);
        }));
    }
}