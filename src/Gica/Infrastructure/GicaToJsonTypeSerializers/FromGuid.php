<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Infrastructure\GicaToJsonTypeSerializers;


use Gica\Serialize\ObjectSerializer\Exception\ValueNotSerializable;
use Gica\Serialize\ObjectSerializer\Serializer;
use Gica\Types\Guid;
use MongoDB\BSON\ObjectID;

class FromGuid implements Serializer
{
    public function serialize($value)
    {
        if (!$value instanceof Guid) {
            throw new ValueNotSerializable();
        }

        return (string)$value;
    }
}