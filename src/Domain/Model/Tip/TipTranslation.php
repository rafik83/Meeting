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
    private $id;
    
    /** @var Tip */
    private $tip;
    
    /** @var string  */
    private $title;
    
    /** @var string */
    private $lang;
    
    /** @var string */
    private $content;

    /**
     * TipTranslation constructor.
     *
     * @param Tip    $tip
     * @param string $title
     * @param string $lang
     * @param string $content
     */
    public function __construct(Tip $tip, $title, $lang, $content)
    {
        $this->tip     = $tip;
        $this->title   = $title;
        $this->lang    = $lang;
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
