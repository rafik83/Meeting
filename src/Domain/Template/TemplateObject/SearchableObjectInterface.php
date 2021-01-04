<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

interface SearchableObjectInterface
{
    /**
     * @param string|null $locale
     *
     * @return array|string
     */
    public function getSearchableContent(?string $locale = null);
}
