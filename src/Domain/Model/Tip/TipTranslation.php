<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Tip;

class TipTranslation
{
    /** @var int */
    public $id;
    
    /** @var Tip */
    public $tip;
    
    /** @var string  */
    public $title;
    
    /** @var string */
    public $lang;
    
    /** @var string */
    public $content;

    /**
     * TipTranslation constructor.
     *
     * @param Tip    $tip
     * @param string $title
     * @param string $lang
     * @param string $content
     */
    public function __construct(Tip $tip = null, $title = null, $lang = null, $content = null)
    {
        $this->tip     = $tip;
        $this->title   = $title;
        $this->lang    = $lang;
        $this->content = $content;
    }
    
    /**
     * @param string $title
     * @param string $content
     */
    public function update($title, $content)
    {
        $this->title   = $title;
        $this->content = $content;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return Tip
     */
    public function getTip()
    {
        return $this->tip;
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
}
