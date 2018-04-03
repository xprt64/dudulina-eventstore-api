<?php


namespace Gica\Infrastructure\Http\Middleware\RouterMiddleware;


use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    /** @var Route[] */
    private $routes = [];

    public function match(ServerRequestInterface $request): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->match($request)) {
                return $route;
            }
        }

        return null;
    }

    public function matchRoutesIgnoringMethod(ServerRequestInterface $request)
    {
        return array_filter($this->routes, function (Route $route) use ($request) {
            return $route->matchIgnoringMethod($request);
        });
    }

    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    private function findRouteByName($name): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->getRouteName() == $name) {
                return $route;
            }
        }
        return null;
    }

    public function generateUri($name, array $parameters = [])
    {
        $route = $this->findRouteByName($name);

        if(!$route)
        {
            throw new \Exception(sprintf("Route %s not found", $name));
        }

        return $route->replaceParameters($parameters);
    }
}