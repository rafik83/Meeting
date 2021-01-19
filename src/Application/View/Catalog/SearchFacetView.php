<?php

namespace Proximum\Vimeet\Application\View\Catalog;

class SearchFacetView
{
    /** @var string */
    public $label;

    /** @var string */
    public $placeholder;

    /** @var bool */
    public $enabled;

    /** @var string */
    public $type;

    /**
     * SearchFacetView constructor.
     *
     * @param string $type
     * @param string $label
     * @param string $placeholder
     * @param bool   $enabled
     */
    public function __construct($type, $label, $placeholder, $enabled)
    {
        $this->type        = $type;
        $this->label       = $label;
        $this->placeholder = $placeholder;
        $this->enabled     = $enabled;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true === $this->enabled;
    }
}
