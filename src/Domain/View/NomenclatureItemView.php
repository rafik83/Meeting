<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class NomenclatureItemView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param int    $id
     * @param string $title
     * @param string $locale
     */
    public function __construct($id, $title, $locale)
    {
        $this->id     = $id;
        $this->title  = $title;
        $this->locale = $locale;
    }
}
