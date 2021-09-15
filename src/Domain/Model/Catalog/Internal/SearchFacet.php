<?php

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
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    /**
     * @param string $locale
     * @param string $label
     * @param string $placeholder
     *
     * @return SearchFacet
     */
    public function translate($locale, $label, $placeholder): SearchFacet
    {
        if ($this->hasTranslation($locale)) {
            $this->translations->get($locale)->update($label, $placeholder);
        } else {
            $this->translations->add(new SearchFacetTranslation($this, $label, $placeholder, $locale));
        }

        return $this;
    }
}
