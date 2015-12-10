<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class TypeTranslation
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
     * @var Type
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * TypeTranslation constructor.
     *
     * @param Type   $type
     * @param string $locale
     * @param string $title
     */
    public function __construct(Type $type, $locale, $title)
    {
        $this->type   = $type;
        $this->locale = $locale;
        $this->title  = $title;
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

    /**
     * @param string $title
     *
     * @return TypeTranslation
     */
    public function update($title)
    {
        $this->title = $title;

        return $this;
    }
}
