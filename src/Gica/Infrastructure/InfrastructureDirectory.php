<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Infrastructure;

/**
 * Marker class used for directory location
 */
class InfrastructureDirectory
{
    public static function getInfrastructureDirectory()
    {
        return __DIR__;
    }
}