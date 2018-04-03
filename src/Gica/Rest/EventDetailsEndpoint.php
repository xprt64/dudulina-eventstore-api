<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Rest;


use Gica\Rest\Cache\CacheableByDateEndpoint;
use Gica\Rest\Endpoint\EndpointResponse;
use Gica\Rest\Endpoint\EventToJsonSerializer;
use MongoDB\BSON\UTCDateTime;
use Mongolina\MongoEventStore;
use Psr\Http\Message\ServerRequestInterface;

class EventDetailsEndpoint implements CacheableByDateEndpoint
{
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
        EventToJsonSerializer $eventToJsonSerializer
    )
    {
        $this->mongoEventStore = $mongoEventStore;
        $this->eventToJsonSerializer = $eventToJsonSerializer;
    }

    public function __invoke(string $id)
    {
        $beforeFetchTime = microtime(true);

        $eventDocument = $this->mongoEventStore->fetchEventSubDocumentById($id);

        if (!$eventDocument) {
            throw new \Exception('Event not found', 404);
        }

        $event = $this->serializeEvent($eventDocument);

        return new EndpointResponse(
            [
                'event' => $event,
                'stats' => [
                    'pageLoadDuration'   => microtime(true) - PAGE_LOAD_TIME,
                    'queryFetchDuration' => microtime(true) - $beforeFetchTime,
                ],
            ],
            200
        );
    }

    public function getLastModifiedDate(ServerRequestInterface $request):?\DateTimeImmutable
    {
        $eventDocument = $this->mongoEventStore->fetchEventSubDocumentById($request->getAttribute('id'));

        if (!$eventDocument) {
            return null;
        }
        /** @var UTCDateTime $createdAt */
        $createdAt = $eventDocument['createdAt'];
        return \DateTimeImmutable::createFromMutable($createdAt->toDateTime());
    }

    private function serializeEvent($document)
    {
        return $this->eventToJsonSerializer->serializeEvent($document);
    }
}