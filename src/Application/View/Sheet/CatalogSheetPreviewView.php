<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;

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
     * @var PreviewView[]
     */
    public $preview;

    /**
     * @param int    $id
     * @param string $title
     * @param string $type
     * @param array  $preview
     */
    public function __construct($id, $title, $type, array $preview)
    {
        $this->id      = $id;
        $this->title   = $title;
        $this->type    = $type;
        $this->preview = $preview;
    }

    /**
     * @return bool
     */
    public function hasImage()
    {
        foreach ($this->preview as $previewView) {
            if ($previewView->isImage() && !empty($previewView->content)) {
                return true;
            }
        }

        return false;
    }
}
