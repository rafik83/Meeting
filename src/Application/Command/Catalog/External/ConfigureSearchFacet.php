<?php

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Catalog\AbstractSearchFacet;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilterTranslation;
use Proximum\Vimeet\Domain\Model\Event;

abstract class ConfigureSearchFacet implements Command
{
    /** @var Event */
    public $event;

    /** @var AbstractSearchFacet[] */
    public $persistedSearchFacets;

    /** @var array */
    public $searchFacets;

    /** @var CatalogTagFilter[] */
    public $catalogTagFilters = [];

    /** @var string */
    public $type;

    public function __construct(array $catalogTagFilters)
    {
        /** @var CatalogTagFilter $catalogTagFilter */
        foreach ($catalogTagFilters as $key => $catalogTagFilter) {
            $this->catalogTagFilters[$key]['tag'] = $catalogTagFilter->getTag();

            /** @var CatalogTagFilterTranslation $translation */
            foreach ($catalogTagFilter->getTranslations() as $translation) {
                $this->catalogTagFilters[$key]['translations'][$translation->getLocale()] = [
                    'label' => $translation->getLabel(),
                    'placeholder'=>  $translation->getPlaceholder(),
                ];
            }
        }
    }

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
