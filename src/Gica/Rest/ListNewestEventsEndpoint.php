<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Rest;


use Gica\Infrastructure\Http\ParameterInjecterMiddleware\CustomHydrator\MongoTimestampRehydrator;
use Gica\Iterator\IteratorTransformer\IteratorMapper;
use Gica\Rest\Cache\CacheableByDateEndpoint;
use Gica\Rest\Endpoint\EndpointResponse;
use Gica\Rest\Endpoint\EventToJsonSerializer;
use Gica\Rest\Helper\AbsoluteUrlCreator;
use MongoDB\BSON\Timestamp;
use Mongolina\EventSequence;
use Mongolina\MongoAllEventByClassesStream;
use Mongolina\MongoEventStore;
use Psr\Http\Message\ServerRequestInterface;

class ListNewestEventsEndpoint implements CacheableByDateEndpoint
{
    /**
     * @var AbsoluteUrlCreator
     */
    private $absoluteUrlGenerator;
    /**
     * @var MongoEventStore
     */
    private $mongoEventStore;

    /**
     * @var MongoTimestampRehydrator
     */
    private $mongoTimestampRehydrator;
    /**
     * @var EventToJsonSerializer
     */
    private $eventToJsonSerializer;

    public function __construct(
        MongoEventStore $mongoEventStore,
        AbsoluteUrlCreator $absoluteUrlGenerator,
        MongoTimestampRehydrator $mongoTimestampRehydrator,
        EventToJsonSerializer $eventToJsonSerializer
    )
    {
        $this->absoluteUrlGenerator = $absoluteUrlGenerator;
        $this->mongoEventStore = $mongoEventStore;
        $this->mongoTimestampRehydrator = $mongoTimestampRehydrator;
        $this->eventToJsonSerializer = $eventToJsonSerializer;
    }

    public function __invoke(Timestamp $before = null, int $limit = 10, array $classes = [])
    {
        $beforeFetchTime = microtime(true);

        if ($limit <= 0) {
            $limit = 10;
        }

        $events = $this->loadEvents($before, $limit, $classes);

        $links = [
            'first' => $this->absoluteUrlGenerator->generateUri('route.events.newest') . '?limit=' . $limit,
        ];

        if (\count($events) > $limit) {
            $events = \array_slice($events, 0, $limit);
            $lastEvent = $events[$limit - 1];
            $links['before'] = $this->absoluteUrlGenerator->generateUri('route.events.newest') . '?before=' . $lastEvent['meta']['ts'] . '&limit=' . $limit;
        }

        return new EndpointResponse(
            [
                'links'  => $links,
                'events' => $events,
                'stats'  => [
                    'pageLoadDuration'   => microtime(true) - PAGE_LOAD_TIME,
                    'queryFetchDuration' => microtime(true) - $beforeFetchTime,
                ],
            ],
            200
        );
    }

    private function fetchEventsFromStream(MongoAllEventByClassesStream $stream): array
    {
        $iterator = $this->factoryEventExtractorIterator();
        return iterator_to_array($iterator($stream->getCursorForEvents()), false);
    }

    private function factoryEventExtractorIterator(): IteratorMapper
    {
        return new IteratorMapper(function ($document) {
            return $this->eventToJsonSerializer->serializeEvent($document);
        });
    }

    public function getLastModifiedDate(ServerRequestInterface $request):?\DateTimeImmutable
    {
        $get = $request->getQueryParams();

        $after = null;
        if ($get['after']) {
            $after = $this->mongoTimestampRehydrator->hydrateTimestamp($get['after']);
        }

        $events = $this->loadEvents($after, (int)$get['limit'], $get['classes'] ?: []);
        $lastEvent = $events[\count($events) - 1];

        return new \DateTimeImmutable($lastEvent['meta']['createdAt']);
    }

    private function loadEvents(?Timestamp $before, int $limit, array $classes): array
    {
        $fetchLimit = $limit + 1;

        /** @var MongoAllEventByClassesStream $stream */
        $stream = $this->mongoEventStore->loadEventsByClassNames($classes);

        $stream->beforeSequence(new EventSequence($before ?? new Timestamp(1, (int)(time() * 1.1)), 0));
        $stream->sort(false);
        $stream->limitCommits($fetchLimit);

        return $this->fetchEventsFromStream($stream);
    }
}