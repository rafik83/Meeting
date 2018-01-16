<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\SearchFacet;

use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class Update
{
    /** @var array */
    public $searchFacets;

    /** @var SearchFacet[] */
    public $persistedSearchFacets;

    /** @var Event */
    public $event;

    /**
     * @param Event         $event
     * @param SearchFacet[] $searchFacets
     */
    public function __construct(Event $event, array $searchFacets)
    {
        $this->event = $event;
        $this->persistedSearchFacets = $searchFacets;

        $types = SearchFacet::getAllTypes();
        foreach ($types as $type) {
            foreach ($searchFacets as $searchFacet) {
                if ($searchFacet->getType() === $type) {
                    $translations = [];

                    foreach ($this->event->getLocales() as $locale) {
                        $translations[$locale] = [
                            'label'       => $searchFacet->getLabel($locale),
                            'placeholder' => $searchFacet->getPlaceholder($locale),
                            'type'        => $type,
                        ];
                    }

                    $this->searchFacets[$type] = [
                        'enabled'      => $searchFacet->isEnabled(),
                        'translations' => $translations,
                    ];
                }
            }

            if (!isset($this->searchFacets[$type])) {
                $translations = [];

                foreach ($this->event->getLocales() as $locale) {
                    $translations[$locale] = [
                        'label'       => '',
                        'placeholder' => '',
                        'type'        => $type,
                    ];
                }

                $this->searchFacets[$type] = [
                    'enabled'      => false,
                    'translations' => $translations,
                ];
            }
        }
    }
}
