<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

interface IndexableObjectInterface
{
    /**
     * @return array|string
     */
    public function getSearchableContent();
}
