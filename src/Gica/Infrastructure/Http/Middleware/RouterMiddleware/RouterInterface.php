<?php


namespace Gica\Infrastructure\Http\Middleware\RouterMiddleware;


use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{

    public function match(ServerRequestInterface $request): ?Route;

    /**
     * @param ServerRequestInterface $request
     * @return Route[]
     */
    public function matchRoutesIgnoringMethod(ServerRequestInterface $request);

    public function addRoute(Route $route);

    public function generateUri($name, array $parameters = []);
}