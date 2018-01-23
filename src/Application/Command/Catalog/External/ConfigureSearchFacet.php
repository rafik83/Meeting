<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\AbstractSearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class ConfigureSearchFacet
{
    /** @var Event */
    public $event;

    /** @var AbstractSearchFacet[] */
    public $persistedSearchFacets;

    /** @var array */
    public $searchFacets;

    public function prepareSearchFacetFields()
    {
        $types = AbstractSearchFacet::getAllTypes();
        foreach ($types as $type) {
            foreach ($this->persistedSearchFacets as $searchFacet) {
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
