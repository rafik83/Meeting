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

class DescriptionTranslation
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
    private $description;

    /**
     * CategoryTranslation constructor.
     *
     * @param Happening $happening
     * @param string    $locale
     * @param string    $description
     */
    public function __construct(Happening $happening, $locale, $description)
    {
        $this->happening   = $happening;
        $this->locale      = $locale;
        $this->description = $description;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $description
     *
     * @return CategoryTranslation
     */
    public function update($description)
    {
        $this->description = $description;

        return $this;
    }
}
