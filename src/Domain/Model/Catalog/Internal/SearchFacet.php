<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Catalog\Internal;

use Proximum\Vimeet\Domain\Model\Catalog\AbstractSearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class SearchFacet extends AbstractSearchFacet
{
    /**
     * SearchFacet constructor.
     *
     * @param Event  $event
     * @param string $type
     * @param bool   $enabled
     */
    public function __construct(Event $event, $type, $enabled = false)
    {
        parent::__construct($event, $type, $enabled);

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = new SearchFacetTranslation($this, '', '', $locale);
        }
    }

    /**
     * @return SearchFacetTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations->toArray();
    }
}
