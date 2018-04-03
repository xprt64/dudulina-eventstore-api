<?php
/******************************************************************************
 * Copyright (c) 2018 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Infrastructure\Http\ParameterInjecterMiddleware\CustomHydrator;


use Gica\Infrastructure\Http\ParameterInjecterMiddleware\CustomHydrator;

use MongoDB\BSON\Timestamp;

class MongoTimestampRehydrator implements CustomHydrator
{

    /**
     * @inheritdoc
     */
    public function tryToHydrateFromValue(\ReflectionClass $reflectionClass, $value)
    {
        if (!$reflectionClass->isSubclassOf(Timestamp::class) && $reflectionClass->name != Timestamp::class) {
            throw new \InvalidArgumentException();
        }

        return $this->hydrateTimestamp($value);
    }

    public function hydrateTimestamp($value): Timestamp
    {
        list($a, $b) = explode(':', trim($value, '[]'));

        $a = $a < 0 ? 0 : $a;
        $b = $b < 0 ? 0 : $b;

        return new Timestamp((int)$a, (int)$b);
    }
}