<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\Happening;

class TitleTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Happening
     */
    private $happening;

    /**
     * @var string
     */
    private $title;

    /**
     * CategoryTranslation constructor.
     *
     * @param Happening $happening
     * @param string    $locale
     * @param string    $title
     */
    public function __construct(Happening $happening, $locale, $title)
    {
        $this->happening = $happening;
        $this->locale    = $locale;
        $this->title     = $title;
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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Happening
     */
    public function getHappening()
    {
        return $this->happening;
    }

    /**
     * @param Happening $happening
     */
    public function setHappening($happening)
    {
        $this->happening = $happening;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $title
     *
     * @return TitleTranslation
     */
    public function update($title)
    {
        $this->title = $title;

        return $this;
    }
}
