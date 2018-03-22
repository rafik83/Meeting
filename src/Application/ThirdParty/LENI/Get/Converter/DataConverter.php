<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter;

use Proximum\Vimeet\Domain\Model\Event;

/**
 * Merge all LENI to Vimeet data indexed by tag with main and custom data converters
 */
class DataConverter
{
    /** @var MainDataConverter */
    private $mainDataConverter;

    public function __construct(MainDataConverter $mainDataConverter)
    {
        $this->mainDataConverter = $mainDataConverter;
    }

    /**
     * @param Event $event
     * @param array $rawUser
     *
     * @return array indexed by tag
     */
    public function convert(Event $event, array $rawUser): array
    {
        $dataIndexedByTag = $this->mainDataConverter->convert($rawUser);

        // $dataIndexedByTag = array_merge($dataIndexedByTag, $this->customDataConverter->convert($rawUser);

        return $dataIndexedByTag;
    }
}
