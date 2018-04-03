<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\Middleware\Exception\MethodNotAccepted;
use Gica\Infrastructure\Http\Middleware\RouterMiddleware\Route;
use Gica\Infrastructure\Http\Middleware\RouterMiddleware\RouterInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * In case there are routes that match the path but do not match the method respond with a 405 error
 */
class MethodNotAcceptedMiddleware implements MiddlewareInterface
{

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        RouterInterface $router
    )
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $routes = $this->router->matchRoutesIgnoringMethod($request);
        if ($routes) {
            throw new MethodNotAccepted($request->getMethod(), $this->extractMethodsFromRoutes($routes));
        }

        return $delegate->process($request);
    }

    /**
     * @param Route[] $routes
     * @return string[]
     */
    private function extractMethodsFromRoutes($routes)
    {
        $result = [];

        foreach ($routes as $route) {
            $result = array_merge($result, $route->getMethods());
        }

        return array_unique($result);
    }
}