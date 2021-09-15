<?php

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class Configure extends ConfigureSearchFacet
{
    /** @var Type[] */
    public $types;

    /** @var Category[] */
    public $categories;

    /** @var bool */
    public $externalCatalogEnabled;

    /** @var CatalogVisibility */
    public $catalogVisibility;

    /** @var bool */
    public $hasMessage;

    /** @var array of CatalogVisibilityTranslation index by locale */
    public $messageTranslations;

    /** @var null|string */
    public $registrationUrl;

    /**
     * @param Event              $event
     * @param CatalogVisibility  $catalogVisibility
     * @param SearchFacet[]      $searchFacets
     * @param CatalogTagFilter[] $catalogTagFilters
     */
    public function __construct(
        Event $event,
        CatalogVisibility $catalogVisibility,
        array $searchFacets,
        array $catalogTagFilters
    ) {
        parent::__construct($catalogTagFilters);

        $this->type = CatalogTagFilter::TYPE_EXTERNAL;
        $this->event = $event;

        foreach ($event->getLocales() as $locale) {
            if (null !== ($catalogVisibilityTranslation = $catalogVisibility->getMessage($locale))) {
                $this->messageTranslations[$locale]['title']   = $catalogVisibilityTranslation->getTitle();
                $this->messageTranslations[$locale]['content'] = $catalogVisibilityTranslation->getContent();
            } else {
                $this->messageTranslations[$locale]['title']   = '';
                $this->messageTranslations[$locale]['content'] = '';
            }
        }

        $this->persistedSearchFacets = $searchFacets;
        $this->catalogVisibility      = $catalogVisibility;
        $this->externalCatalogEnabled = $event->isExternalCatalogEnabled();
        $this->types                  = $catalogVisibility->getTypes();
        $this->categories             = $catalogVisibility->getCategories();
        $this->hasMessage             = $catalogVisibility->hasMessage();
        $this->registrationUrl        = $catalogVisibility->getRegistrationUrl();

        $this->prepareSearchFacetFields();
    }
}
