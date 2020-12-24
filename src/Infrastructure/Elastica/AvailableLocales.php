<?php

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
