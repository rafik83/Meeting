<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class SheetCatalogView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var array
     */
    public $template = [];

    /**
     * SheetCatalogView constructor.
     *
     * @param int   $id
     * @param array $data
     * @param array $template
     */
    public function __construct($id, array $data, array $template)
    {
        $this->id       = $id;
        $this->data     = $data;
        $this->template = $template;
    }
}
