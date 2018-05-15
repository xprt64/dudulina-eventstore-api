<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

use Gica\Infrastructure\Http\Endpoint\EndpointFactory;
use Gica\Infrastructure\Http\Middleware\AuthenticationMiddleware;
use Gica\Infrastructure\Http\Middleware\Factory;
use Gica\Infrastructure\Http\Middleware\RouteMiddleware;
use Gica\Infrastructure\Http\Middleware\RouterMiddleware\Router;
use Gica\Infrastructure\Http\Middleware\RouterMiddleware\RouterInterface;
use Zend\ServiceManager\ServiceManager;

/** @var ServiceManager $container */

$router = new Router();

$container->setService(RouterInterface::class, $router);

$routeMiddleware = new RouteMiddleware(
    $router,
    $container->get(Factory::class),
    $container->get(EndpointFactory::class),
    AuthenticationMiddleware::class
);

$routeMiddleware->addRestEndpoint(
    '/', ['GET'], \Gica\Rest\IndexEndpoint::class, false, 'route.index'
);

$routeMiddleware->addRestEndpoint(
    '/events/chronologically', ['GET'], \Gica\Rest\ListEventsChronologicallyEndpoint::class, false, 'route.events.list'
);

$routeMiddleware->addRestEndpoint(
    '/events/most-recent', ['GET'], \Gica\Rest\ListNewestEventsEndpoint::class, false, 'route.events.newest'
);

$routeMiddleware->addRestEndpoint(
    '/events/count', ['GET'], \Gica\Rest\CountEventsEndpoint::class, false, 'route.events.count'
);

$routeMiddleware->addRestEndpoint(
    '/events/{id}', ['GET'], \Gica\Rest\EventDetailsEndpoint::class, false, 'route.event.details'
);

$routeMiddleware->addRestEndpoint(
    '/aggregate', ['GET'], \Gica\Rest\AggregateEventsEndpoint::class, false, 'route.aggregate.stream'
);

return $routeMiddleware;