<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Dependency;


use MongoDB\Client;
use Psr\Container\ContainerInterface;

class MongoDatabaseFactory
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function selectDatabase()
    {
        $dsn = $this->container->get('config')['mongoEventStoreDsn'];

        if (!preg_match('#^mongodb://(.*?)/(?P<database>[a-z0-9_\-]+)#ims', $dsn, $matches)) {
            throw new \Exception("Could not find event store database name in $dsn");
        }
        $client = $this->createClient($dsn);
        return $client->selectDatabase($matches['database']);
    }

    public function selectEventsCollection()
    {
        return $this->selectDatabase()->selectCollection('eventStore');
    }

    public function createClient(string $dsn)
    {
        return new Client($dsn, [], [
            'typeMap' => [
                'array'    => 'array',
                'document' => 'array',
                'root'     => 'array',
            ],
        ]);
    }
}