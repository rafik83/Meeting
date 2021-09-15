<?php

namespace Proximum\Vimeet\Application\View\Catalog;

class LocalizationView
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
     * LocalizationView constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->id   = strtolower(str_replace(' ', '-', $name));
        $this->name = $name;
    }
}
