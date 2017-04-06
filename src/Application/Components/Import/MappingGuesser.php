<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Import;

class MappingGuesser
{
    /**
     * A mapping array of mappedIn keys and their mappedOut keys
     *
     * @var array
     */
    private $mappings;

    /**
     * MappingGuesser constructor.
     *
     * @param array $mappings
     */
    public function __construct(array $mappings)
    {
        $this->mappings  = $mappings;
    }

    /**
     * @param $mappedOutKey
     *
     * @return false|int
     */
    public function getMappedInKey($mappedOutKey)
    {
        $mappedInKey = array_search($mappedOutKey, $this->mappings);

        return $mappedInKey;
    }

    /**
     * @param int $mappedInKey
     *
     * @return string|false
     */
    public function getMappedOutKey($mappedInKey)
    {
        if (!array_key_exists($mappedInKey, $this->mappings)) {
            return false;
        }

        return $this->mappings[$mappedInKey];
    }
}
