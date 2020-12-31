<?php

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;

class SheetListView
{
    /** @var int */
    public $id;

    /**
     * "Titre fiche / Société"
     *
     * @var string
     */
    public $title;

    /**
     * "Etat de la fiche"
     *
     * @var string
     */
    public $state;

    /** @var string */
    public $validationState;

    /**
     * "Catégorie"
     *
     * @var array
     */
    public $categories;

    /**
     * "Type de participation"
     *
     * @var string
     */
    public $type;

    /**
     * "Nom, prénom, email du propriétaire de la fiche"
     *
     * @var SheetParticipantView
     */
    public $owner;

    /**
     * "Suivi commercial"
     *
     * @var string
     */
    public $follower;

    /**
     * "Statut commercial"
     *
     * @var string|null
     */
    public $commercialStatus;

    /**
     * "Date de création de la fiche"
     *
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * "Date de dernière connexion d'un des participants"
     *
     * @var \DateTimeInterface
     */
    public $lastLoginAt;

    /** @var string */
    public $impersonationToken;

    /** @var string */
    public $traceAction;

    /** @var \DateTimeInterface|null */
    public $traceAt = null;

    /** @var string|null */
    public $traceBy = null;

    /** @var int */
    public $completeness;

    /** @var bool */
    public $enabled;

    /** @var bool */
    public $inCatalog;

    /** @var string */
    public $spotReference;

    /** @var int */
    public $countParticipant;

    /** @var bool */
    public $hasGroup;

    /** @var null|string */
    public $groupTitle;

    /** @var bool */
    public $attend;

    /** @var null|\DateTimeInterface */
    public $reminderDate;

    /** @var string[] $inkedSheetsTitle */
    public $linkedSheetsTitle;

    public function __construct(
        int $id,
        string $title,
        string $state,
        string $validationState,
        int $completeness,
        bool $enabled,
        bool $inCatalog,
        bool $attend,
        array $categories,
        array $linkedSheetsTitle,
        string $type,
        SheetParticipantView $owner,
        string $follower,
        ?string $commercialStatus = null,
        ?\DateTimeInterface $reminderDate = null,
        \DateTimeInterface $createdAt,
        ?\DateTimeInterface $lastLoginAt = null,
        int $countParticipant,
        ?bool $hasGroup = false,
        ?string $groupTitle = null,
        ?string $spotReference = null,
        ?Trace $trace = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->state = $state;
        $this->validationState = $validationState;
        $this->completeness = $completeness;
        $this->enabled = $enabled;
        $this->inCatalog = $inCatalog;
        $this->attend = $attend;
        $this->categories = $categories;
        $this->type = $type;
        $this->owner = $owner;
        $this->follower = $follower;
        $this->commercialStatus = $commercialStatus;
        $this->reminderDate = $reminderDate;
        $this->createdAt = $createdAt;
        $this->lastLoginAt = $lastLoginAt;
        $this->spotReference = $spotReference;
        $this->countParticipant = $countParticipant;
        $this->hasGroup = $hasGroup;
        $this->groupTitle = $groupTitle;

        if (null !== $trace) {
            $this->traceAction = $trace->getAction();
            $this->traceAt = $trace->getDate();
            $this->traceBy = $trace->getAuthor();
        }
        $this->linkedSheetsTitle = $linkedSheetsTitle;
    }

    /**
     * @return bool
     */
    public function isIncomplete()
    {
        return 100 !== $this->completeness;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true === $this->enabled;
    }

    /**
     * @return bool
     */
    public function isInCatalog()
    {
        return $this->inCatalog;
    }

    /**
     * @return bool
     */
    public function hasCommercialStatus(): bool
    {
        return null !== $this->commercialStatus;
    }

    /**
     * @return bool
     */
    public function hasReminderDate(): bool
    {
        return null !== $this->reminderDate;
    }

    /**
     * @return string
     */
    public function getCommercialStatusLabel(): string
    {
        if (isset(CommercialStatus::STATUS_WITH_LABEL[$this->commercialStatus])) {
            return CommercialStatus::STATUS_WITH_LABEL[$this->commercialStatus];
        }

        return 'default';
    }

    /**
     * @return string
     */
    public function completenessStatus()
    {
        return Sheet::getCompletenessStatus($this->completeness);
    }

    /**
     * @return bool
     */
    public function hasTraceLabel()
    {
        return (Sheet::STATE_VALIDATED === $this->state || Sheet::STATE_ACCEPTED === $this->state) && !empty($this->traceAction);
    }
}
