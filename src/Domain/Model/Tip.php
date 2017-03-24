<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class Tip
{
    /** @var int */
    private $id;
    
    /** @var string  */
    private $title;
    
    /** @var string */
    private $lang;
    
    /** @var string */
    private $content;
    
    /** @var bool */
    private $onMeetingManagement;
    
    /** @var bool */
    private $onCatalog;
    
    /** @var bool */
    private $onPrintPlanning;
    
    /**
     * Tip constructor.
     *
     * @param string    $title
     * @param string    $lang
     * @param string    $content
     * @param bool      $onMeetingManagement
     * @param bool      $onCatalog
     * @param bool      $onPrintPlanning
     */
    public function __construct($title, $lang, $content, $onMeetingManagement, $onCatalog, $onPrintPlanning)
    {
        $this->title                = $title;
        $this->lang                 = $lang;
        $this->content              = $content;
        $this->onMeetingManagement  = $onMeetingManagement;
        $this->onCatalog            = $onCatalog;
        $this->onPrintPlanning      = $onPrintPlanning;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }
    
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * @return bool
     */
    public function isOnMeetingManagement()
    {
        return $this->onMeetingManagement;
    }
    
    /**
     * @return bool
     */
    public function isOnCatalog()
    {
        return $this->onCatalog;
    }
    
    /**
     * @return bool
     */
    public function isOnPrintPlanning()
    {
        return $this->onPrintPlanning;
    }
}
