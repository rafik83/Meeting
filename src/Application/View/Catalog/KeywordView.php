<?php

namespace Proximum\Vimeet\Application\View\Catalog;

class KeywordView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * KeywordView constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->id   = strtolower(str_replace(' ', '-', $name));
        $this->name = $name;
    }
}
