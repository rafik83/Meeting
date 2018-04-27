<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Elastica;

abstract class AvailableLocales
{
    /**
     * @return array
     */
    public static function getAvailableLocalesForContent()
    {
        return ['fr', 'en'];
    }
}
