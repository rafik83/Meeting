<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Configure
{
    /** @var Event */
    public $event;

    /** @var Type[] */
    public $types;

    /** @var Category[] */
    public $categories;

    /** @var bool */
    public $externalCatalogEnabled;

    /** @var SearchFacet[] */
    public $persistedSearchFacets;

    /** @var array */
    public $searchFacets;

    /** @var CatalogVisibility */
    public $catalogVisibility;

    /** @var bool */
    public $hasMessage;

    /** @var array of CatalogVisibilityTranslation index by locale */
    public $messageTranslations;

    /** @var null|string */
    public $registrationUrl;

    /**
     * @param Event             $event
     * @param CatalogVisibility $catalogVisibility
     * @param SearchFacet[]     $searchFacets
     */
    public function __construct(Event $event, CatalogVisibility $catalogVisibility, array $searchFacets)
    {
        $this->event                  = $event;
        $this->catalogVisibility      = $catalogVisibility;
        $this->externalCatalogEnabled = $event->isExternalCatalogEnabled();
        $this->types                  = $catalogVisibility->getTypes();
        $this->categories             = $catalogVisibility->getCategories();
        $this->hasMessage             = $catalogVisibility->hasMessage();
        $this->registrationUrl        = $catalogVisibility->getRegistrationUrl();

        foreach ($event->getLocales() as $locale) {
            if (($catalogVisibilityTranslation = $catalogVisibility->getMessage($locale)) !== null) {
                $this->messageTranslations[$locale]['title']   = $catalogVisibilityTranslation->getTitle();
                $this->messageTranslations[$locale]['content'] = $catalogVisibilityTranslation->getContent();
            } else {
                $this->messageTranslations[$locale]['title']   = '';
                $this->messageTranslations[$locale]['content'] = '';
            }
        }

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
