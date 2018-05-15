<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Rest;


use Dudulina\Aggregate\AggregateDescriptor;
use Gica\Iterator\IteratorTransformer\IteratorExpander;
use Gica\Rest\Endpoint\EndpointResponse;
use Gica\Rest\Endpoint\EventToJsonSerializer;
use Gica\Rest\Helper\AbsoluteUrlCreator;
use Gica\Types\Guid;
use MongoDB\Driver\Cursor;
use Mongolina\MongoEventStore;

class AggregateEventsEndpoint
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
     * @var EventToJsonSerializer
     */
    private $eventToJsonSerializer;

    public function __construct(
        MongoEventStore $mongoEventStore,
        AbsoluteUrlCreator $absoluteUrlGenerator,
        EventToJsonSerializer $eventToJsonSerializer
    )
    {
        $this->absoluteUrlGenerator = $absoluteUrlGenerator;
        $this->mongoEventStore = $mongoEventStore;
        $this->eventToJsonSerializer = $eventToJsonSerializer;
    }

    public function __invoke(string $aggregateClass, Guid $aggregateId, int $after = -1, int $limit = 10)
    {

        $aggregateClass = str_replace('\\\\', '\\', $aggregateClass);

        $descriptor = new AggregateDescriptor($aggregateId, $aggregateClass);

        if ($limit <= 0) {
            $limit = 10;
        }

        $beforeFetchTime = microtime(true);
        $events = $this->loadEvents($descriptor, $after, $limit);

        $links = [
            'first' => $this->absoluteUrlGenerator->generateUri('route.aggregate.stream') . '?aggregateClass=' . $aggregateClass . '&aggregateId=' . $aggregateId . '&after=-1' . '&limit=' . $limit,
        ];

        $lastEvent = $events[\count($events) - 1];

        $headers = [];

        if ($lastEvent) {
            $nextEvents = $this->loadEvents($descriptor, $lastEvent['aggregate']['version'], 1);
            if ($nextEvents) {
                $links['next'] = $this->absoluteUrlGenerator->generateUri('route.aggregate.stream') . '?aggregateClass=' . $aggregateClass . '&aggregateId=' . $aggregateId . '&after=' . $lastEvent['aggregate']['version'] . '&limit=' . $limit;
                $headers['Cache-Control'] = 'public';
                $headers['Expires'] = gmdate('D, d M Y H:i:s \G\M\T', strtotime($lastEvent['meta']['createdAt']) + 365 * 24 * 60 * 60);
            }
            $headers['Last-modified'] = gmdate('D, d M Y H:i:s \G\M\T', strtotime($lastEvent['meta']['createdAt']));
        }

        return new EndpointResponse(
            [
                'events' => $events,
                'links'  => $links,
                'stats'  => [
                    'pageLoadDuration'   => microtime(true) - PAGE_LOAD_TIME,
                    'queryFetchDuration' => microtime(true) - $beforeFetchTime,
                ],
            ],
            200,
            $headers
        );
    }

    private function fetchEventsFromCursor(Cursor $cursor): array
    {
        $iterator = $this->factoryEventExtractorIterator();
        return iterator_to_array($iterator($cursor), false);
    }

    private function factoryEventExtractorIterator():IteratorExpander
    {
        return new IteratorExpander(function ($document) {
            foreach ($document[MongoEventStore::EVENTS] as $eventSubDocument) {
                $newDocument = $document;
                $newDocument[MongoEventStore::EVENTS] = $eventSubDocument;
                yield $this->eventToJsonSerializer->serializeEvent($newDocument);
            }
        });
    }

    private function loadEvents(AggregateDescriptor $aggregateDescriptor, int $after, int $limit): array
    {
        /** @var \Mongolina\MongoAggregateAllEventStream $stream */
        $stream = $this->mongoEventStore->loadEventsForAggregate($aggregateDescriptor);

        return $this->fetchEventsFromCursor($stream->getCursorGreaterThanToSomeVersion($after, $limit));
    }
}