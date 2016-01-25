<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Happening\Category as CategoryHappening;
use Proximum\Vimeet\Domain\Model\Happening\DescriptionTranslation;
use Proximum\Vimeet\Domain\Model\Happening\TitleTranslation;

class Happening
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var CategoryHappening
     */
    private $category;

    /**
     * @var \DateTimeInterface
     */
    private $begin;

    /**
     * @var \DateTimeInterface
     */
    private $end;

    /**
     * @var ArrayCollection
     */
    private $titleTranslations;

    /**
     * @var ArrayCollection
     */
    private $descriptionTranslations;

    /**
     * Happening constructor.
     *
     * @param Event              $event
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param CategoryHappening  $category
     */
    public function __construct(Event $event, \DateTimeInterface $begin, \DateTimeInterface $end, CategoryHappening $category)
    {
        $this->event                   = $event;
        $this->begin                   = $begin;
        $this->end                     = $end;
        $this->category                = $category;
        $this->titleTranslations       = new ArrayCollection();
        $this->descriptionTranslations = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return CategoryHappening
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param CategoryHappening $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Get begin.
     *
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end.
     *
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param TitleTranslation $titleTranslation
     */
    public function setTitleTranslation(TitleTranslation $titleTranslation)
    {
        $this->titleTranslations->set($titleTranslation->getLocale(), $titleTranslation);
    }

    /**
     * @param DescriptionTranslation $descriptionTranslation
     */
    public function setDescriptionTranslation(DescriptionTranslation $descriptionTranslation)
    {
        $this->descriptionTranslations->set($descriptionTranslation->getLocale(), $descriptionTranslation);
    }

    /**
     * @return ArrayCollection
     */
    public function getTitleTranslations()
    {
        return $this->titleTranslations;
    }

    /**
     * @return ArrayCollection
     */
    public function getDescriptionTranslations()
    {
        return $this->descriptionTranslations;
    }
}
