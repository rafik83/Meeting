<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class NomenclatureItemTranslation
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
     * @var NomenclatureItem
     */
    private $nomenclatureItem;

    /**
     * @var string
     */
    private $title;

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
     * @return NomenclatureItem
     */
    public function getNomenclatureItem()
    {
        return $this->nomenclatureItem;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
