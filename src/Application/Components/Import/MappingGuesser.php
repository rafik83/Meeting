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
     * @var array
     */
    private $mappings;

    /**
     * @var array
     */
    private $mappedIn;

    /**
     * @var array
     */
    private $mappedOut;

    /**
     * MappingGuesser constructor.
     *
     * @param array $mappings
     * @param array $mappedIn
     * @param array $mappedOut
     */
    public function __construct(array $mappings, array $mappedIn, array $mappedOut)
    {
        $this->mappings  = $mappings;
        $this->mappedIn  = $mappedIn;
        $this->mappedOut = $mappedOut;
    }

    /**
     * @param string $mappedOutValue
     *
     * @return int|false
     */
    public function getMappedInKey($mappedOutValue)
    {
        $mappedOutKey = array_search($mappedOutValue, $this->mappedOut);
        $mappedInKey  = array_search($mappedOutKey, $this->mappings);

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
