<?php

namespace Proximum\Vimeet\Application\View\Package;

class FeatureView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @param string $title
     * @param string $description
     */
    public function __construct($title, $description)
    {
        $this->title       = $title;
        $this->description = $description;
    }
}
