<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog;

class SearchFacetView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $placeholder;

    /**
     * @var bool
     */
    private $state;

    /**
     * @var string
     */
    private $type;

    /**
     * SearchFacetView constructor.
     *
     * @param string $type
     * @param string $label
     * @param string $placeholder
     * @param bool   $state
     */
    public function __construct($type, $label, $placeholder, $state)
    {
        $this->type        = $type;
        $this->label       = $label;
        $this->placeholder = $placeholder;
        $this->state       = $state;
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
        return $this->state === true;
    }
}
