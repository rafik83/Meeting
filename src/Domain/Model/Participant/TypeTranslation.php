<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Participant;

use Proximum\Vimeet\Domain\Model\Participant\Type;

class TypeTranslation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @return integer
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
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
