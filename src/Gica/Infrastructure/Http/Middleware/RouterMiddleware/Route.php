<?php


namespace Gica\Infrastructure\Http\Middleware\RouterMiddleware;


use Psr\Http\Message\ServerRequestInterface;

class Route
{
    /**
     * @var string
     */
    private $path;
    private $middleware;
    private $methods;
    /**
     * @var string
     */
    private $routeName;

    public function __construct(
        string $path,
        $middleware,
        $methods,
        string $routeName = null
    )
    {
        $this->path = $path;
        $this->middleware = $middleware;
        $this->methods = $this->normalizeMethods(is_array($methods) ? $methods : (null === $methods ? ['*'] : [$methods]));
        $this->routeName = $routeName;
    }

    public function matchIgnoringMethod(ServerRequestInterface $request): bool
    {
        return preg_match($this->makeRegexFromPath($this->path), $request->getUri()->getPath());
    }

    public function matchMethod(ServerRequestInterface $request): bool
    {
        return in_array($this->normalizeMethod($request->getMethod()), $this->methods);
    }

    public function match(ServerRequestInterface $request): bool
    {
        return $this->matchMethod($request) && $this->matchIgnoringMethod($request);
    }

    public function getParameters(ServerRequestInterface $request): array
    {
        preg_match($this->makeRegexFromPath($this->path), $request->getUri()->getPath(), $matches);

        return $matches;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function getMatchedMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @return string[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    private function normalizeMethods($methods)
    {
        return array_map(function ($method) {
            return $this->normalizeMethod($method);
        }, $methods);
    }

    private function normalizeMethod($method)
    {
        return strtoupper($method);
    }

    private function makeRegexFromPath($path)
    {
        return '#^' . preg_replace_callback('#\{([a-z0-9_]+)\}#ims', function ($matches) {
                return '(?P<' . preg_quote($matches[1]) . '>[^/]*)';
            }, $path) . '$#ims';
    }

    public function replaceParameters(array $parameters)
    {
        $wrappedParameters = array_map(function ($parameter) {
            return '{' . $parameter . '}';
        }, array_keys($parameters));

        return str_replace($wrappedParameters, array_values($parameters), $this->path);
    }
}