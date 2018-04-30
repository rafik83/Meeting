<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Catalog;

use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Sheet;

abstract class AbstractSheetPreviewView
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
     * @var Sheet
     */
    public $sheet;

    /**
     * AbstractSheetPreviewView constructor.
     *
     * @param int           $id
     * @param string|null   $title
     * @param string|null   $type
     * @param PreviewView[] $preview
     * @param Sheet         $sheet
     */
    public function __construct(int $id, $title, $type, array $preview, Sheet $sheet)
    {
        $this->id      = $id;
        $this->title   = $title;
        $this->type    = $type;
        $this->preview = $preview;
        $this->sheet   = $sheet;
    }
}
