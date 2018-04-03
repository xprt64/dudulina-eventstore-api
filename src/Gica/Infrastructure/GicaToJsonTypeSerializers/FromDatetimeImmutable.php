<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Infrastructure\GicaToJsonTypeSerializers;


use Gica\Serialize\ObjectSerializer\Exception\ValueNotSerializable;
use Gica\Serialize\ObjectSerializer\Serializer;
use MongoDB\BSON\UTCDateTime;

class FromDatetimeImmutable implements Serializer
{
    public function serialize($value)
    {
        if (!$value instanceof \DateTimeImmutable) {
            throw new ValueNotSerializable();
        }

        return $value->format('c');
    }
}