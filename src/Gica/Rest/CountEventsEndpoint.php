<?php
/**
 * Copyright (c) 2018. Galbenu Constantin <xprt64@gmail.com>
 */

namespace Gica\Rest;


use Gica\Rest\Endpoint\EndpointResponse;
use MongoDB\BSON\Timestamp;
use Mongolina\MongoAllEventByClassesStream;
use Mongolina\MongoEventStore;

class CountEventsEndpoint
{
    /**
     * @var MongoEventStore
     */
    private $mongoEventStore;


    public function __construct(
        MongoEventStore $mongoEventStore
    )
    {
        $this->mongoEventStore = $mongoEventStore;
    }

    public function __invoke(Timestamp $before = null, Timestamp $after = null, array $classes = [])
    {
        $beforeFetchTime = microtime(true);
        $events = $this->countEvents($before, $after, $classes);
        return new EndpointResponse(
            [
                'count' => $events,
                'stats' => [
                    'pageLoadDuration'   => microtime(true) - PAGE_LOAD_TIME,
                    'queryFetchDuration' => microtime(true) - $beforeFetchTime,
                ],
            ],
            200
        );
    }

    private function countEvents(?Timestamp $before, ?Timestamp $after, array $classes): int
    {
        /** @var MongoAllEventByClassesStream $stream */
        $stream = $this->mongoEventStore->loadEventsByClassNames($classes);
        if ($before) {
            $stream->beforeSequence($before);
        }
        if ($after) {
            $stream->afterSequence($before);
        }
        return $stream->countCommits();
    }
}