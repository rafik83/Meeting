<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Template\TemplateObject;

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
     * @var TemplateObject[]
     */
    public $preview;

    /**
     * @var Meeting\Request|null
     */
    public $meetingRequest;

    /**
     * @param int             $id
     * @param string          $title
     * @param string          $type
     * @param array           $preview
     * @param Meeting\Request $meetingRequest
     */
    public function __construct($id, $title, $type, array $preview, Meeting\Request $meetingRequest = null)
    {
        $this->id             = $id;
        $this->title          = $title;
        $this->type           = $type;
        $this->preview        = $preview;
        $this->meetingRequest = $meetingRequest;
    }

    /**
     * @return bool
     */
    public function hasMeetingRequest()
    {
        return null !== $this->meetingRequest;
    }
}
