<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

use Dudulina\EventStore;
use Gica\Infrastructure\Dependency\ConstructorBasedAbstractFactory;
use Gica\Infrastructure\Dependency\Http\AbsoluteUrlCreatorFactory;
use Gica\Infrastructure\Dependency\Http\AuthenticationMiddlewareFactory;
use Gica\Infrastructure\Dependency\Http\ParameterInjecterFactory;
use Gica\Infrastructure\Dependency\Http\ResponseNegociatorFactory;
use Gica\Infrastructure\Dependency\MongoDatabaseFactory;
use Gica\Infrastructure\Http\ContentNegociation\ResponseNegociator;
use Gica\Infrastructure\Http\Endpoint\CachingEndpointFactory;
use Gica\Infrastructure\Http\Endpoint\EndpointFactory;
use Gica\Infrastructure\Http\Middleware\AuthenticationMiddleware;
use Gica\Infrastructure\Http\ParameterInjecterMiddleware\ParameterInjecter;
use Gica\Rest\Helper\AbsoluteUrlCreator;
use Gica\Serialize\ObjectSerializer\CompositeSerializer;
use Mongolina\EventsCommit\CommitSerializer;
use Mongolina\MongoAggregateAllEventStreamFactory;
use Mongolina\MongoAllEventByClassesStreamFactory;
use Mongolina\MongoEventStore;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\ServiceManager\ServiceManager;

return new ServiceManager([
    'services'           => [
        'config' => require __DIR__ . '/index.php',
    ],
    'invokables'         => [
        EmitterInterface::class => SapiEmitter::class,

        /**
         * Persistence factories
         */
    ],
    'factories'          => [
        ResponseNegociator::class               => ResponseNegociatorFactory::class,
        ParameterInjecter::class                => ParameterInjecterFactory::class,
        AuthenticationMiddleware::class         => AuthenticationMiddlewareFactory::class,
        AbsoluteUrlCreator::class               => AbsoluteUrlCreatorFactory::class,

        /**
         * CQRS+Event sourcing factories
         */
        MongoEventStore::class                       => function (ContainerInterface $container) {
            $database = (new MongoDatabaseFactory($container))->selectDatabase();

            return new MongoEventStore(
                $database->selectCollection('eventStore'),
                $container->get(MongoAggregateAllEventStreamFactory::class),
                $container->get(MongoAllEventByClassesStreamFactory::class),
                $container->get(CommitSerializer::class)
            );
        },

        \Gica\Serialize\ObjectSerializer\Serializer::class => function(ContainerInterface $container){
            return new CompositeSerializer([]);
        },

        \Gica\Dependency\AbstractFactory::class => function ($container) {
            return new \Gica\Dependency\ConstructorAbstractFactory($container);
        },
        \Psr\Log\LoggerInterface::class         => function (ContainerInterface $container) {
            $log = new Logger('name');
            $log->pushHandler(new StreamHandler('/var/log/app/warning.log', Logger::WARNING));
            $log->pushHandler(new StreamHandler('/var/log/app/error.log', Logger::ERROR));
            return $log;
        },
        // remove the following line to disable cached rest endpoints
        EndpointFactory::class                  => function (ContainerInterface $container) {
            return new CachingEndpointFactory($container);
        },
    ],
    'abstract_factories' => [
        ConstructorBasedAbstractFactory::class,
    ],
    'delegators'         => [],
    'shared'             => [],
    'shared_by_default'  => true,
]);
