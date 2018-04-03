<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Infrastructure;


use Gica\Infrastructure\GicaToJsonTypeSerializers\FromMongoGuid;
use Gica\Infrastructure\GicaToJsonTypeSerializers\FromUTCDatetime;
use Gica\Serialize\ObjectSerializer\CompositeSerializer;
use Gica\Serialize\ObjectSerializer\ObjectSerializer;
use Gica\Infrastructure\GicaToJsonTypeSerializers\FromDatetimeImmutable;
use Gica\Infrastructure\GicaToJsonTypeSerializers\FromEnum;
use Gica\Infrastructure\GicaToJsonTypeSerializers\FromGuid;
use Gica\Infrastructure\GicaToJsonTypeSerializers\FromSet;

class ToJsonObjectSerializer extends ObjectSerializer
{
    public function __construct()
    {
        parent::__construct(new CompositeSerializer([
            new FromEnum(),
            new FromGuid(),
            new FromMongoGuid(),
            new FromSet(),
            new FromDatetimeImmutable(),
            new FromUTCDatetime(),
        ]));
    }
}