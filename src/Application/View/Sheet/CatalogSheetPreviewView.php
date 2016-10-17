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
use Proximum\Vimeet\Domain\Model\Sheet;
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
     * @var Meeting\Request|null
     */
    public $meetingRequest;

    /**
     * @var bool
     */
    public $isItMySheet;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param int             $id
     * @param Sheet           $sheet
     * @param string          $title
     * @param string          $type
     * @param array           $preview
     * @param Meeting\Request $meetingRequest
     * @param bool            $isItMySheet
     */
    public function __construct(
        $id,
        Sheet $sheet,
        $title, $type,
        array $preview,
        Meeting\Request $meetingRequest = null,
        $isItMySheet
    ) {
        $this->id             = $id;
        $this->sheet          = $sheet;
        $this->title          = $title;
        $this->type           = $type;
        $this->preview        = $preview;
        $this->meetingRequest = $meetingRequest;
        $this->isItMySheet    = $isItMySheet;
    }

    /**
     * @return bool
     */
    public function hasMeetingRequest()
    {
        return null !== $this->meetingRequest;
    }


    /**
     * @return bool
     */
    public function meetingRequestIsPending()
    {
        return $this->meetingRequest->isSent();
    }

    /**
     * @return bool
     */
    public function meetingRequestIsProposition()
    {
        return $this->meetingRequest->getFromSheet() === $this->sheet;
    }
}
