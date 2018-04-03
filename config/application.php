<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */
use Gica\Infrastructure\Http\Application;
use Gica\Infrastructure\Http\Middleware\AbsoluteUrlsMiddleware;
use Gica\Infrastructure\Http\Middleware\CacheMiddleware;
use Gica\Infrastructure\Http\Middleware\ContentNegociationMiddleware;
use Gica\Infrastructure\Http\Middleware\ErrorsMiddleware;
use Gica\Infrastructure\Http\Middleware\FormBodyParamsMiddleware;
use Gica\Infrastructure\Http\Middleware\JsonBodyParamsMiddleware;
use Gica\Infrastructure\Http\Middleware\MethodNotAcceptedMiddleware;
use Gica\Infrastructure\Http\Middleware\NotFoundEndpoint;
use Gica\Infrastructure\Http\Middleware\OptionsMiddleware;
use Zend\ServiceManager\ServiceManager;

/** @var ServiceManager $container */

// our application
$application = $container->get(Application::class);

//activate cache
$application->pipe(CacheMiddleware::class);

//it permits absolute uris to be generated
$application->pipe(OptionsMiddleware::class);

//it permits absolute uris to be generated
$application->pipe(AbsoluteUrlsMiddleware::class);

// negociate with the client the accepted format (i.e. json, xml etc)
$application->pipe(ContentNegociationMiddleware::class);

//catch errors and return them to the client in its accepted format
$application->pipe(ErrorsMiddleware::class);

$application->pipe(FormBodyParamsMiddleware::class);
$application->pipe(JsonBodyParamsMiddleware::class);

$application->pipe(require __DIR__ . '/../config/routes.php');

$application->pipe(MethodNotAcceptedMiddleware::class);

$application->pipe(NotFoundEndpoint::class);

return $application;