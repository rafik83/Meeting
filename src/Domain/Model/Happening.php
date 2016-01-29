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
use Proximum\Vimeet\Domain\Model\Happening\HappeningTranslation;

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
    private $translations;

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
        $this->event        = $event;
        $this->begin        = $begin;
        $this->end          = $end;
        $this->category     = $category;
        $this->translations = new ArrayCollection();
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
     * @param \DateTimeInterface $begin
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
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
     * @param \DateTimeInterface $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @param string $locale
     */
    public function getTitle($locale)
    {
        return $this->getTranslations()->get($locale)->getTitle();
    }

    /**
     * @param HappeningTranslation $translation
     */
    public function setTranslation(HappeningTranslation $translation)
    {
        $this->translations->set($translation->getLocale(), $translation);
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param CategoryHappening  $category
     */
    public function update(\DateTimeInterface $begin, \DateTimeInterface $end, CategoryHappening $category)
    {
        $this->begin    = $begin;
        $this->end      = $end;
        $this->category = $category;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $description
     */
    public function updateTranslation($locale, $title, $description)
    {
        $this->translations->get($locale)->update($title, $description);
    }
}
