<?php

namespace Proximum\Vimeet\Infrastructure\Elastica;

class SheetContentView
{
    /** @var array */
    public $contentByLocale;

    /** @var array */
    public $content;

    public function __construct(array $contentByLocale, array $content)
    {
        $this->contentByLocale = $contentByLocale;
        $this->content = $content;
    }
}
