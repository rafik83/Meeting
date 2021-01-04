<?php

namespace Proximum\Vimeet\Application\View\Catalog\Aggregat;

class NomenclatureTagView
{
    /** @var string */
    public $key;

    /** @var string */
    public $title;

    /**
     * @param string $key
     * @param string $title
     */
    public function __construct(string $key, string $title)
    {
        $this->key   = $key;
        $this->title = $title;
    }
}
