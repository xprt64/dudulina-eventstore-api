<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Infrastructure\Http\ParameterInjecterMiddleware;

use Gica\Rest\Endpoint\EndpointResponse;
use Psr\Http\Message\ServerRequestInterface;

class ParameterInjecter
{

    private $variables;

    /** @var \ReflectionClass */
    private $middlewareClass;
    /**
     * @var CustomHydrator
     */
    private $customHydrator;

    public function __construct(
        CustomHydrator $customHydrator
    )
    {
        $this->customHydrator = $customHydrator;
    }

    public function injectParametersFromRequestAndReturnEndpointResponse(ServerRequestInterface $request, $endpointInstance): EndpointResponse
    {
        $className = get_class($endpointInstance);

        $this->middlewareClass = new \ReflectionClass($className);

        if (!$this->middlewareClass->hasMethod('__invoke')) {
            throw new \Exception("Endpoint is not invokable");
        }

        $invoke = $this->middlewareClass->getMethod('__invoke');

        $get = $request->getQueryParams();
        $body = $request->getParsedBody();
        $this->variables = [
            'request' => $request,
            'isPost'  => 'POST' == $request->getMethod(),
            'isGet'   => 'GET' == $request->getMethod(),
            'get'     => $get,
            'body'    => $body,
        ];

        $callArguments = [];

        foreach ($invoke->getParameters() as $parameter) {
            $rawValue = $this->getValue($request, $parameter, $get, $body);

            if ($rawValue === '') {
                $rawValue = null;
            }

            $callArguments[] = $this->parseParameter($rawValue, $parameter, $invoke);
        }

        unset($this->variables);
        unset($this->middlewareClass);

        return call_user_func_array($endpointInstance, $callArguments);
    }

    private function parseParameter($rawValue, \ReflectionParameter $parameter, \ReflectionMethod $method)
    {
        $name = $parameter->getName();
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        }

        if (!$parameter->hasType()) {
            if (null === $rawValue) {
                if ($parameter->isDefaultValueAvailable()) {
                    return $parameter->getDefaultValue();
                }
            }
            return $rawValue;
        } else {
            if (null === $rawValue) {
                if (!$parameter->getType()->allowsNull()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        return $parameter->getDefaultValue();
                    } else {
                        throw new \Exception("Parameter $name must not be null");
                    }
                } else {
                    return null;
                }
            }

            if ('array' == $parameter->getType()) {
                return $this->hydrateArrayWithBuiltinValue($rawValue);
            } else if ($this->isScalar((string)$parameter->getType())) {
                return $this->hydrateBuiltinValue($rawValue, $parameter->getType());
            } else {
                return $this->hydrateCustomValue($rawValue, $parameter->getClass(), $parameter->name);
            }
        }
    }

    private function isScalar(string $shortType): bool
    {
        return in_array($shortType, ['bool', 'string', 'int', 'null', 'float', 'double']);
    }

    private function hydrateBuiltinValue($rawValue, string $type)
    {
        switch ($type) {
            case 'string':
                return (string)($rawValue);
            case 'int':
                return \intval($rawValue, 10);
            case 'double':
            case 'float':
                return (float)($rawValue);
            case 'raw':
            default:
                return $rawValue;
            case 'bool':
                return (bool)($rawValue);
        }
    }

    private function hydrateArrayWithBuiltinValue($rawValue)
    {
        return array_map(function ($item) {
            return $this->hydrateBuiltinValue($item, 'raw');
        }, $rawValue);
    }

    private function hydrateCustomValue($rawValue, \ReflectionClass $reflectionClass, string $parameterName)
    {
        try {
            return $this->customHydrator->tryToHydrateFromValue($reflectionClass, $rawValue);
        } catch (\Exception $exception) {
            throw new \Exception("unkown custom value for {$parameterName}@{$reflectionClass->name}: {$exception->getMessage()}");
        }
    }

    private function getValue(ServerRequestInterface $request, \ReflectionParameter $parameter, $get, $body)
    {
        if (preg_match('#^(.+)Body$#ims', $parameter->getName(), $m) || preg_match('#^(.+)Post#ims', $parameter->getName(), $m)) {
            return isset($body[$m[1]]) ? $body[$m[1]] : null;
        }

        return (null !== $request->getAttribute($parameter->name)) ? $request->getAttribute($parameter->name) : @$get[$parameter->name];
    }
}