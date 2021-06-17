<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

use DateTimeInterface;

class HappeningView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $categoryTitle;

    /** @var DateTimeInterface */
    public $begin;

    /** @var DateTimeInterface */
    public $end;

    /** @var bool */
    public $questionAllowed;

    /** @var int|null */
    public $limit;

    /** @var int */
    public $participations;

    /** @var SpeakerView[] */
    public $speakers;

    /** @var bool */
    public $isPrivate;

    /** @var bool */
    public $hasProducts;

    /** @var bool */
    public $isWebinar;

    /** @var bool */
    public $isInteractiveWebinar;

    /** @var bool */
    public $isVideoWebinar;

    /** @var bool */
    public $isWebinarRecorded;

    /** @var bool */
    public $isWebinarRecordAvailable;

    /** @var string|null */
    public $webinarRecordZipFileUrl;

    public bool $pollAllowed;

    public function __construct(
        int $id,
        string $title,
        string $categoryTitle,
        DateTimeInterface $begin,
        DateTimeInterface $end,
        bool $questionAllowed,
        ?int $limit = null,
        int $participations = 0,
        array $speakers = [],
        bool $isPrivate = false,
        bool $hasProducts = false,
        bool $isWebinar = false,
        bool $isInteractiveWebinar = false,
        bool $isVideoWebinar = false,
        bool $isWebinarRecorded = true,
        bool $isWebinarRecordAvailable = false,
        ?string $webinarRecordZipFileUrl = null,
        bool $pollAllowed = false
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->categoryTitle = $categoryTitle;
        $this->begin = $begin;
        $this->end = $end;
        $this->questionAllowed = $questionAllowed;
        $this->limit = $limit;
        $this->participations = $participations;
        $this->speakers = $speakers;
        $this->isPrivate = $isPrivate;
        $this->hasProducts = $hasProducts;
        $this->isWebinar = $isWebinar;
        $this->isInteractiveWebinar = $isInteractiveWebinar;
        $this->isVideoWebinar = $isVideoWebinar;
        $this->isWebinarRecorded = $isWebinarRecorded;
        $this->isWebinarRecordAvailable = $isWebinarRecordAvailable;
        $this->webinarRecordZipFileUrl = $webinarRecordZipFileUrl;
        $this->pollAllowed = $pollAllowed;
    }

    public function hasLimit(): bool
    {
        return null !== $this->limit;
    }

    public function hasWebinarRecordZipFileUrl(): bool
    {
        return !empty($this->webinarRecordZipFileUrl);
    }
}
