<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\Middleware\RouterMiddleware\Route;
use Gica\Infrastructure\Http\Middleware\RouterMiddleware\RouterInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

/**
 * Handles the request with a OPTIONS method by responding with a empty response
 * and with Accept header containing the accepted methods, if there is at least one route that would match the path.
 * Otherwise, it delegates the control
 */
class OptionsMiddleware implements MiddlewareInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ('OPTIONS' != $request->getMethod()) {
            return $delegate->process($request);

        }

        $routes = $this->router->matchRoutesIgnoringMethod($request);

        if ($routes) {
            return (new Response('php://memory', 200))->withHeader('Accept', implode(',', $this->extractMethodsFromRoutes($routes)));
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