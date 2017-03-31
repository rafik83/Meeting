<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Tip;

class TipView
{
    /** @var int */
    public $id;
    
    /** @var string */
    public $title;
    
    /** @var boolean */
    public $isOnMeetingManagement;
    
    /** @var boolean */
    public $isOnCatalog;
    
    /** @var boolean */
    public $isOnPrintPlanning;
    
    /**
     * TipView constructor.
     *
     * @param int       $id
     * @param string    $title
     * @param bool      $isOnMeetingManagement
     * @param bool      $isOnCatalog
     * @param bool      $isOnPrintPlanning
     */
    public function __construct($id, $title, $isOnMeetingManagement, $isOnCatalog, $isOnPrintPlanning)
    {
        $this->id                    = $id;
        $this->title                 = $title;
        $this->isOnMeetingManagement = $isOnMeetingManagement;
        $this->isOnCatalog           = $isOnCatalog;
        $this->isOnPrintPlanning     = $isOnPrintPlanning;
    }
}
