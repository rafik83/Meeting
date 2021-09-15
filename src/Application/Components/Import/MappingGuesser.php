<?php

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
        return array_search($mappedOutKey, $this->mappings);
    }

    /**
     * @param int $mappedInKey
     *
     * @return string|false
     */
    public function getMappedOutKey($mappedInKey)
    {
        return $this->mappings[$mappedInKey] ?? false;
    }
}
