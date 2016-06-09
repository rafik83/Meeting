<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PromotionCodeTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var PromotionCode
     */
    private $promotionCode;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * PromotionCodeTranslation constructor.
     *
     * @param PromotionCode $promotionCode
     * @param string        $locale
     * @param string        $title
     * @param string        $description
     */
    public function __construct(PromotionCode $promotionCode, $locale, $title, $description)
    {
        $this->promotionCode = $promotionCode;
        $this->locale        = $locale;
        $this->title         = $title;
        $this->description   = $description;
    }

    /**
     * @param string $title
     * @param string $description
     */
    public function update($title, $description)
    {
        $this->title       = $title;
        $this->description = $description;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get promotionCode
     *
     * @return PromotionCode
     */
    public function getPromotionCode()
    {
        return $this->promotionCode;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
