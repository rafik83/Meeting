<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Doctrine\Common\Collections\ArrayCollection;

class Category
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $picto;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
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
    public function getPicto()
    {
        return $this->picto;
    }

    /**
     * @param string $picto
     */
    public function setPicto($picto)
    {
        $this->picto = $picto;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param ArrayCollection $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }
}
