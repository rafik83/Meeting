<?php

namespace Proximum\Vimeet\Domain\UserEventView;

class UserEventSheetsListView
{
    /** @var int */
    public $id;

    /** @var null|string */
    public $title;

    /** @var bool */
    public $isOwner;

    /** @var string */
    public $typeTitle;

    /** @var string */
    public $categoriesTitle;

    /** @var bool */
    public $isEnabled;

    /** @var string */
    public $state;

    /** @var string */
    public $validationState;

    /** @var int */
    public $completeness;

    /** @var string */
    public $completenessStatus;

    /** @var bool */
    public $attend;

    /** @var bool */
    public $hasGroup;

    /** @var null|string */
    public $groupTitle;

    /** @var bool */
    public $isInCatalog;

    /** @var string */
    public $followerName;

    /** @var string */
    public $commercialStatus;

    /** @var string */
    public $commercialStatusLabel;

    public function __construct(
        int $id,
        ?string $title,
        bool $isOwner,
        string $typeTitle,
        string $categoriesTitle,
        bool $isEnabled,
        string $state,
        string $validationState,
        int $completeness,
        string $completenessStatus,
        bool $attend,
        bool $hasGroup,
        ?string $groupTitle,
        bool $isInCatalog,
        ?string $followerName,
        string $commercialStatus,
        string $commercialStatusLabel
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->isOwner = $isOwner;
        $this->typeTitle = $typeTitle;
        $this->categoriesTitle = $categoriesTitle;
        $this->isEnabled = $isEnabled;
        $this->state = $state;
        $this->validationState = $validationState;
        $this->completeness = $completeness;
        $this->completenessStatus = $completenessStatus;
        $this->attend = $attend;
        $this->hasGroup = $hasGroup;
        $this->groupTitle = $groupTitle;
        $this->isInCatalog = $isInCatalog;
        $this->followerName = $followerName;
        $this->commercialStatus = $commercialStatus;
        $this->commercialStatusLabel = $commercialStatusLabel;
    }
}
