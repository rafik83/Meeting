<?php

namespace Proximum\Vimeet\Application\View\Catalog;

class TagFilterView
{
    /** @var string */
    public $tag;

    /** @var string|null */
    public $label;

    /** @var string|null */
    public $placeholder;

    public function __construct(string $tag, ?string $label = null, ?string $placeholder = null)
    {
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->tag = $tag;
    }
}
