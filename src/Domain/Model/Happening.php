<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

/**
 * Domain language: "Conférence"  (aka "Sous-événement")
 */
class Happening implements TimeRangeInterface
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var CategoryHappening */
    private $category;

    /** @var \DateTimeInterface */
    private $begin;

    /** @var \DateTimeInterface */
    private $end;

    /** @var ArrayCollection of HappeningTranslation */
    private $translations;

    /** @var ArrayCollection */
    private $talkings;

    /** @var bool */
    private $questionAllowed = false;

    /** @var int|null */
    private $limitParticipant;

    /** @var ArrayCollection of HappeningParticipation */
    private $participations;

    /** @var ArrayCollection of Question */
    private $questions;

    /** @var ArrayCollection of Type that can access this Happening */
    private $types;

    /** @var null|string */
    private $invitationCode;

    /** @var ArrayCollection of Product */
    private $products;

    /** @var bool */
    private $webinar;

    /** @var bool */
    private $interactiveWebinar;

    /** @var null|string */
    private $webinarSessionId;

    /** @var null|string */
    private $liveUrl;

    /** @var bool */
    private $sidebarAllowed = true;    

    public function __construct(
        Event $event,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        CategoryHappening $category,
        array $types,
        bool $questionAllowed = false,       
        ?int $limitParticipant = null,
        ?string $invitationCode = null,
        bool $webinar = false,
        bool $interactiveWebinar = false,
        ?string $liveUrl = null,
        bool $sidebarAllowed = true 
    ) {
        $this->event = $event;
        $this->begin = $begin;
        $this->end = $end;
        $this->category = $category;
        $this->translations = new ArrayCollection();
        $this->talkings = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->types = new ArrayCollection($types);
        $this->questionAllowed = $questionAllowed;        
        $this->limitParticipant = $limitParticipant;
        $this->invitationCode = $invitationCode;
        $this->products = new ArrayCollection();
        $this->webinar = $webinar;
        $this->interactiveWebinar = $interactiveWebinar;
        $this->liveUrl = $liveUrl;
        $this->sidebarAllowed = $sidebarAllowed;
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

    public function getWebinarHeaderImage($locale): ?string
    {
        /** @var null|HappeningTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return null;
        }

        return $translation->getWebinarHeaderImage();
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

    public function update(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        CategoryHappening $category,
        array $types,
        bool $questionAllowed,      
        ?int $limitParticipant,
        bool $webinar,
        bool $interactiveWebinar,
        ?string $invitationCode = null,
        ?string $liveUrl = null,
        bool $sidebarAllowed
    ) {
        $this->begin = $begin;
        $this->end = $end;
        $this->types = new ArrayCollection($types);
        $this->category = $category;
        $this->questionAllowed = $questionAllowed;       
        $this->limitParticipant = $limitParticipant;
        $this->invitationCode = $invitationCode;
        $this->webinar = $webinar;
        $this->interactiveWebinar = $interactiveWebinar;
        $this->liveUrl = $liveUrl;
        $this->sidebarAllowed = $sidebarAllowed;
    }

    public function updateTranslation(string $locale, string $title, ?string $description, ?string $webinarHeaderImage)
    {
        /** @var HappeningTranslation $translation */
        $translation = $this->translations->get($locale);
        $translation->update($title, $description, $webinarHeaderImage);
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

    public function hasSpeaker(User $user): bool
    {
        /** @var Talking $talking */
        foreach ($this->getTalkings() as $talking) {
            if ($talking->getSpeaker()->getUser() === $user) {
                return true;
            }
        }

        return false;
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

    /**
     * @return bool
     */
    public function isQuestionAllowed()
    {
        return $this->questionAllowed;
    }

    /**
     * @param bool $questionAllowed
     */
    public function setQuestionAllowed($questionAllowed)
    {
        $this->questionAllowed = $questionAllowed;
    }

    /**
     * @return bool
     */
    public function isParticipantLimited()
    {
        return null !== $this->limitParticipant;
    }

    /**
     * @return int|null
     */
    public function getLimitParticipant()
    {
        return $this->limitParticipant;
    }

    /**
     * @param int|null $limitParticipant
     */
    public function setLimitParticipant($limitParticipant)
    {
        $this->limitParticipant = $limitParticipant;
    }

    /**
     * @return HappeningParticipation[]
     */
    public function getParticipations()
    {
        return $this->participations->toArray();
    }

    /**
     * @return ArrayCollection of Question
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param HappeningParticipation[] $participations
     */
    public function setParticipations(array $participations)
    {
        $this->participations = new ArrayCollection($participations);
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    /**
     * @return null|string
     */
    public function getInvitationCode(): ?string
    {
        return $this->invitationCode;
    }

    /**
     * @return bool
     */
    public function hasInvitationCode(): bool
    {
        return null !== $this->invitationCode;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->hasInvitationCode();
    }

    /**
     * @param string $invitationCode
     */
    public function setInvitationCode(string $invitationCode)
    {
        $this->invitationCode = $invitationCode;
    }

    /**
     * @return bool
     */
    public function isSidebarAllowed()
    {
        return $this->sidebarAllowed;
    }   

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products->toArray();
    }

    public function addProduct(Product $product): void
    {
        $this->products->add($product);
    }

    public function removeProduct(Product $product): void
    {
        $this->products->removeElement($product);
    }

    public function hasProducts(): bool
    {
        return !$this->products->isEmpty();
    }

    public function hasWebinarSessionId(): bool
    {
        return null !== $this->webinarSessionId;
    }

    public function getWebinarSessionId(): ?string
    {
        return $this->webinarSessionId;
    }

    public function setWebinarSessionId(string $webinarSessionId): void
    {
        $this->webinarSessionId = $webinarSessionId;
    }

    public function isWebinar(): bool
    {
        return $this->webinar || $this->interactiveWebinar;
    }

    public function isInteractiveWebinar(): bool
    {
        return $this->interactiveWebinar;
    }

    public function getLiveUrl(): ?string
    {
        return $this->liveUrl;
    }
}
