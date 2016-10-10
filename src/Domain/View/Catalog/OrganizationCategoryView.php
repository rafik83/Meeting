<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Catalog;

class OrganizationCategoryView
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $title;

    /**
     * @param string $key
     * @param string $title
     */
    public function __construct($key, $title)
    {
        $this->key   = $key;
        $this->title = $title;
    }
}
