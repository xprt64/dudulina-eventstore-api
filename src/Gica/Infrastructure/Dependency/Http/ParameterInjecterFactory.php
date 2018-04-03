<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Dependency\Http;


use Gica\Infrastructure\Http\ParameterInjecterMiddleware\CustomHydrator\CompositeHydrator;
use Gica\Infrastructure\Http\ParameterInjecterMiddleware\CustomHydrator\MongoTimestampRehydrator;
use Gica\Infrastructure\Http\ParameterInjecterMiddleware\CustomHydrator\StaticMethodFromString;
use Gica\Infrastructure\Http\ParameterInjecterMiddleware\ParameterInjecter;
use Psr\Container\ContainerInterface;

class ParameterInjecterFactory
{
    function __invoke(ContainerInterface $container)
    {
        return new ParameterInjecter(
            new CompositeHydrator([
                new StaticMethodFromString(),
                new MongoTimestampRehydrator(),
            ])
        );
    }
}