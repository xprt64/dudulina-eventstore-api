<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

use Psr\Container\ContainerInterface;
use Whoops\Handler\PrettyPageHandler;
use Zend\Diactoros\ServerRequestFactory;
use Zend\ServiceManager\ServiceManager;

define('PAGE_LOAD_TIME', microtime(true));

require_once __DIR__ . '/../vendor/autoload.php';

/** @var ServiceManager $container */
$container = require_once __DIR__ . '/../config/composition-root.php';

$container->setService(ContainerInterface::class, $container);

$request = ServerRequestFactory::fromGlobals();

if (getenv('DEBUG')) {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new PrettyPageHandler());
    $whoops->register();
}

$application = require_once __DIR__ . '/../config/application.php';

//prevent global use
unset($container);

$application->processPipeAndEmitResponse($request);