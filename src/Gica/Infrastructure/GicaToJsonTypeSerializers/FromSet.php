<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Infrastructure\GicaToJsonTypeSerializers;


use Gica\Serialize\ObjectSerializer\Exception\ValueNotSerializable;
use Gica\Serialize\ObjectSerializer\Serializer;
use Gica\Types\Set;

class FromSet implements Serializer
{
    public function serialize($value)
    {
        if (!$value instanceof Set) {
            throw new ValueNotSerializable();
        }

        return $value->toPrimitive();
    }
}