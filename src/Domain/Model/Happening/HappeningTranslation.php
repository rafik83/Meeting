<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\Happening;

class HappeningTranslation
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
     * @var null|string
     */
    private $description;

    /**
     * @var null|string
     */
    private $webinarHeaderImage;

    public function __construct(
        Happening $happening,
        string $locale,
        string $title,
        ?string $description,
        ?string $webinarHeaderImage = null
    ) {
        $this->happening = $happening;
        $this->locale = $locale;
        $this->title = $title;
        $this->description = $description;
        $this->webinarHeaderImage = $webinarHeaderImage;
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

    public function getWebinarHeaderImage(): ?string
    {
        return $this->webinarHeaderImage;
    }

    public function update(string $title, ?string $description, ?string $webinarHeaderImage): void
    {
        $this->title = $title;
        $this->description = $description;
        $this->webinarHeaderImage = $webinarHeaderImage;
    }
}
