<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Proximum\Vimeet\Domain\Model\Happening\Category as CategoryHappening;
use Proximum\Vimeet\Domain\Model\Happening\HappeningTranslation;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\Talking;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

/**
 * Domain language: "Conférence"  (aka "Sous-événement")
 */
class Happening implements TimeRangeInterface, ChatMessageLinkableInterface
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

    /** @var bool */
    private $videoWebinar;

    /** @var null|string */
    private $webinarSessionId;

    /** @var null|string */
    private $liveUrl;

    /** @var bool */
    private $webinarRecorded;

    /** @var bool */
    private $sidebarAllowed = true;

    /** @var null|string */
    private $webinarRecordZipFileUrl = null;

    /** @var bool */
    private $allowHls;

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
        bool $videoWebinar = false,
        ?string $liveUrl = null,
        bool $sidebarAllowed = true,
        bool $webinarRecorded = true,
        bool $allowHls = false
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
        $this->videoWebinar = $videoWebinar;
        $this->liveUrl = $liveUrl;
        $this->webinarRecorded = $webinarRecorded;
        $this->sidebarAllowed = $sidebarAllowed;
        $this->allowHls = $allowHls;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjectType(): string
    {
        return ChatMessage::TYPE_HAPPENING;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getCategory(): CategoryHappening
    {
        return $this->category;
    }

    public function setCategory(CategoryHappening $category): void
    {
        $this->category = $category;
    }

    public function getBegin(): \DateTimeInterface
    {
        return $this->begin;
    }

    public function setBegin(\DateTimeInterface $begin): void
    {
        $this->begin = $begin;
    }

    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): void
    {
        $this->end = $end;
    }

    public function getTitle(string $locale): string
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getTitle() : '';
    }

    public function getDescription(string $locale): string
    {
        if ($this->translations->containsKey($locale)) {
            $description = $this->translations->get($locale)->getDescription();

            return $description ?? '';
        }

        return '';
    }

    public function getWebinarHeaderImage(string $locale): ?string
    {
        /** @var null|HappeningTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return null;
        }

        return $translation->getWebinarHeaderImage();
    }

    public function setTranslation(HappeningTranslation $translation): void
    {
        $this->translations->set($translation->getLocale(), $translation);
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function isWebinarRecorded(): bool
    {
        return $this->webinarRecorded;
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
        bool $videoWebinar,
        ?string $invitationCode = null,
        ?string $liveUrl = null,
        bool $sidebarAllowed,
        bool $webinarRecorded = true,
        bool $allowHls = true
    ): void {
        $this->begin = $begin;
        $this->end = $end;
        $this->types = new ArrayCollection($types);
        $this->category = $category;
        $this->questionAllowed = $questionAllowed;
        $this->limitParticipant = $limitParticipant;
        $this->invitationCode = $invitationCode;
        $this->webinar = $webinar;
        $this->interactiveWebinar = $interactiveWebinar;
        $this->videoWebinar = $videoWebinar;
        $this->liveUrl = $liveUrl;
        $this->sidebarAllowed = $sidebarAllowed;
        $this->webinarRecorded = $webinarRecorded;
        $this->allowHls = $allowHls;
    }

    public function updateTranslation(
        string $locale,
        string $title,
        ?string $description,
        ?string $webinarHeaderImage
    ): void {
        /** @var HappeningTranslation $translation */
        $translation = $this->translations->get($locale);
        $translation->update($title, $description, $webinarHeaderImage);
    }

    public function getTalkings(): Collection
    {
        return $this->talkings;
    }

    /**
     * @return Speaker[]
     */
    public function getSpeakers(): array
    {
        return $this
            ->talkings
            ->matching(Criteria::create()->orderBy(['position' => 'ASC']))
            ->map(static function (Talking $talking) {
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

    public function setSpeakers(array $speakers): void
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
    }

    public function isQuestionAllowed(): bool
    {
        return $this->questionAllowed;
    }

    public function setQuestionAllowed(bool $questionAllowed): void
    {
        $this->questionAllowed = $questionAllowed;
    }

    public function isParticipantLimited(): bool
    {
        return null !== $this->limitParticipant;
    }

    public function getLimitParticipant(): ?int
    {
        return $this->limitParticipant;
    }

    public function setLimitParticipant(?int $limitParticipant): void
    {
        $this->limitParticipant = $limitParticipant;
    }

    /**
     * @return HappeningParticipation[]
     */
    public function getParticipations(): array
    {
        return $this->participations->toArray();
    }

    /**
     * @return ArrayCollection of Question
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @param HappeningParticipation[] $participations
     */
    public function setParticipations(array $participations): void
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

    public function getInvitationCode(): ?string
    {
        return $this->invitationCode;
    }

    public function hasInvitationCode(): bool
    {
        return null !== $this->invitationCode;
    }

    public function isPrivate(): bool
    {
        return $this->hasInvitationCode();
    }

    public function setInvitationCode(string $invitationCode): void
    {
        $this->invitationCode = $invitationCode;
    }

    public function isSidebarAllowed() : bool
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
        return $this->webinar || $this->interactiveWebinar || $this->videoWebinar;
    }

    public function isInteractiveWebinar(): bool
    {
        return $this->interactiveWebinar;
    }

    public function isVideoWebinar(): bool
    {
        return $this->videoWebinar;
    }

    public function getLiveUrl(): ?string
    {
        return $this->liveUrl;
    }

    public function isVideoWebinarAndHasLiveUrl(): bool
    {
        return $this->isVideoWebinar() && null !== $this->getLiveUrl();
    }

    public function addWebinarRecordZipFileUrl(?string $url): void
    {
        $this->webinarRecordZipFileUrl = $url;
    }

    public function hasWebinarRecordZipFileUrl(): bool
    {
        return !empty($this->getWebinarRecordZipFileUrl());
    }

    public function getWebinarRecordZipFileUrl(): ?string
    {
        return $this->webinarRecordZipFileUrl;
    }

    public function allowWebinarOnHLS(): bool
    {
        return $this->allowHls;
    }
}
