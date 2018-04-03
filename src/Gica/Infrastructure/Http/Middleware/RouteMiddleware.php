<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\Endpoint\EndpointFactory;
use Gica\Infrastructure\Http\Middleware\RouterMiddleware\Route;
use Gica\Infrastructure\Http\Middleware\RouterMiddleware\RouterInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The REST router middleware. Every added route can have an authentication middleware called before them.
 * Routes point to REST Endpoints, not normal middleware.
 */
class RouteMiddleware implements MiddlewareInterface
{
    const MATCHED_ROUTE_RESULT_IN_PREDISPATCH = 'matchedRouteResultInPredispatch';
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var Factory
     */
    private $factory;
    /**
     * @var EndpointFactory
     */
    private $endpointFactory;
    /**
     * @var
     */
    private $authenticationMiddleware;
    private $preDispatchMiddleware;

    public function __construct(
        RouterInterface $router,
        Factory $factory,
        EndpointFactory $endpointFactory,
        $authenticationMiddleware,
        $preDispatchMiddleware = null
    )
    {
        $this->router = $router;
        $this->factory = $factory;
        $this->endpointFactory = $endpointFactory;
        $this->authenticationMiddleware = $authenticationMiddleware;
        $this->preDispatchMiddleware = $preDispatchMiddleware;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $matched = $this->router->match($request);

        if (!$matched) {
            return $delegate->process($request);
        }

        $request = $this->applyMathedParametersAsRequestAttributes($request, $matched);

        $middleware = $this->factory->factoryMiddleware($matched->getMiddleware());

        if ($this->preDispatchMiddleware) {
            $middleware = $this->factory->combineMiddlewares([$this->preDispatchMiddleware, $middleware]);
        }

        return $middleware->process($request, $delegate);
    }

    private function applyMathedParametersAsRequestAttributes(ServerRequestInterface $request, Route $matchedRoute): ServerRequestInterface
    {
        foreach ($matchedRoute->getParameters($request) as $k => $v) {
            $request = $request->withAttribute($k, $v);
        }

        return $request;
    }

    public function addRestEndpoint(string $path, $methods, string $middlewareClass, bool $authenticationNecessary, string $routeName)
    {
        $middlewares = [];

        if ($authenticationNecessary) {
            $middlewares[] = $this->authenticationMiddleware;
        }

        $middlewares[] = $this->endpointFactory->createEndpointMiddleware($middlewareClass);

        $this->router->addRoute(new Route(
            $path,
            $middlewares,
            $methods,
            $routeName
        ));
    }
}