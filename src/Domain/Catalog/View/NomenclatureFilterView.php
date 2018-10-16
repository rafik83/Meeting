<?php

namespace Proximum\Vimeet\Domain\Catalog\View;

class NomenclatureFilterView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var array */
    public $items;

    public function __construct(int $id, string $title, array $items)
    {
        $this->id = $id;
        $this->title = $title;
        $this->items = $items;
    }
}
