<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class CatalogSheetPreviewView
{
    /**
     * @var int
     */
    public $id;

    /**
     * "Titre de fiche"
     *
     * @var string
     */
    public $title;

    /**
     * "Type de participation"
     *
     * @var string
     */
    public $type;

    /**
     * @param int    $id
     * @param string $title
     * @param string $type
     */
    public function __construct($id, $title, $type)
    {
        $this->id    = $id;
        $this->title = $title;
        $this->type  = $type;
    }
}
