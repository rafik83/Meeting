<?php

namespace Proximum\Vimeet\Application\View\Catalog\Aggregat;

class NomenclatureTagViews
{
    /** @var string */
    public $tag;

    /** @var array */
    public $nomenclatureTagViews;

    /** @var int */
    public $maxDepth;

    public function __construct(string $tag, array $nomenclatureTagViews, int $maxDepth)
    {
        $this->tag = $tag;
        $this->nomenclatureTagViews = $nomenclatureTagViews;
        $this->maxDepth = $maxDepth;
    }
}
