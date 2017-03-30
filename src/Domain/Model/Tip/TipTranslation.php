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
    public $locale;
    
    /** @var string */
    public $content;

    /**
     * TipTranslation constructor.
     *
     * @param Tip    $tip
     * @param string $title
     * @param string $locale
     * @param string $content
     */
    public function __construct(Tip $tip = null, $title = null, $locale = null, $content = null)
    {
        $this->tip     = $tip;
        $this->title   = $title;
        $this->locale  = $locale;
        $this->content = $content;
    }

    /**
     * @param TipTranslation $tipTranslation
     */
    public function update(TipTranslation $tipTranslation)
    {
        $this->title   = $tipTranslation->title;
        $this->content = $tipTranslation->content;
        $this->locale  = $tipTranslation->locale;
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
    public function getLocale()
    {
        return $this->locale;
    }
    
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
