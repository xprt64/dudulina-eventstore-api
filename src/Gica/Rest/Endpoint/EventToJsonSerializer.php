<?php
/**
 * Copyright (c) 2018. Galbenu Constantin <xprt64@gmail.com>
 */

namespace Gica\Rest\Endpoint;


use Gica\Infrastructure\ToJsonObjectSerializer;
use Gica\Rest\Helper\AbsoluteUrlCreator;

class EventToJsonSerializer
{

    /**
     * @var ToJsonObjectSerializer
     */
    private $toJsonObjectSerializer;
    /**
     * @var AbsoluteUrlCreator
     */
    private $absoluteUrlGenerator;

    public function __construct(
        AbsoluteUrlCreator $absoluteUrlGenerator,
        ToJsonObjectSerializer $toJsonObjectSerializer
    )
    {
        $this->toJsonObjectSerializer = $toJsonObjectSerializer;
        $this->absoluteUrlGenerator = $absoluteUrlGenerator;
    }

    public function serializeEvent($document)
    {
        return $this->toJsonObjectSerializer->convert([
            'id'        => $document['events']['id'],
            'type'      => $document['events']['eventClass'],
            'payload'   => $document['events']['dump'],
            'aggregate' => [
                'id'      => $document['aggregateId'],
                'type'    => $document['aggregateClass'],
                'stream'  => $document['streamName'],
                'version' => $document['version'],
                'links'   => [
                    'stream' => $this->absoluteUrlGenerator->generateUri('route.aggregate.stream') . '?aggregateClass=' . $document['aggregateClass'] . '&aggregateId=' . $document['aggregateId'] . '&after=-1',
                ],
            ],
            'meta'      => [
                'createdAt' => $document['createdAt'],
                'createdBy' => $document['authenticatedUserId'],
                'ts'        => (string)$document['ts'],
                'command'   => $document['commandMeta'],
            ],
            'links'     => [
                'self' => $this->absoluteUrlGenerator->generateUri('route.event.details', ['id' => $document['events']['id']]),
            ],
        ]);
    }
}