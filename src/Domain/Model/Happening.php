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
use Doctrine\Common\Collections\Criteria;
use Proximum\Vimeet\Domain\Model\Happening\Category as CategoryHappening;
use Proximum\Vimeet\Domain\Model\Happening\HappeningTranslation;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\Talking;

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
     * @var ArrayCollection
     */
    private $talkings;

    /**
     * Happening constructor.
     *
     * @param Event              $event
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param CategoryHappening  $category
     */
    public function __construct(
        Event $event,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        CategoryHappening $category
    ) {
        $this->event        = $event;
        $this->begin        = $begin;
        $this->end          = $end;
        $this->category     = $category;
        $this->translations = new ArrayCollection();
        $this->talkings     = new ArrayCollection();
    }

    /**
     * @return int
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
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getTitle() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getDescription() : '';
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

    /**
     * @return ArrayCollection
     */
    public function getTalkings()
    {
        return $this->talkings;
    }

    /**
     * @return Speaker[]
     */
    public function getSpeakers()
    {
        return $this
            ->talkings
            ->matching(Criteria::create()->orderBy(['position' => 'ASC']))
            ->map(function (Talking $talking) {
                return $talking->getSpeaker();
            })
            ->toArray();
    }

    /**
     * @param array $speakers
     *
     * @return Happening
     */
    public function setSpeakers(array $speakers)
    {
        // Make sure a speaker doesn't appear more than once
        $speakers = array_unique($speakers, SORT_REGULAR);

        // Remove surplus of talking
        while ($this->talkings->count() > count($speakers)) {
            $this->talkings->removeElement($this->talkings->last());
        }

        // Add / update talking with speakers and positions
        foreach ($speakers as $position => $speaker) {
            if ($this->talkings->get($position)) {
                $this->talkings->get($position)->update($speaker, $position);
            } else {
                $this->talkings->add(new Talking($speaker, $this, $position));
            }
        }

        return $this;
    }
}
