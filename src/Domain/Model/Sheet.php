<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Exception\Sheet\SheetException;
use Proximum\Vimeet\Domain\Model\Sheet\Analytics;
use Proximum\Vimeet\Domain\Model\Sheet\AvailableSlot;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Sheet\Availability\ConfirmationStatus;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationStatus;
use Proximum\Vimeet\Domain\Trace\TraceableName;

/**
 * "Fiche de participation".
 */
class Sheet implements TraceableInterface
{
    /** Tag used to do a mapping between Sheet's state and third party value */
    public const SHEET_STATE = 'sheet_state';

    const STATE_PENDING = 'pending';
    const STATE_VALIDATED = 'validated';
    const STATE_ACCEPTED = 'accepted';
    const STATE_REFUSED = 'refused';

    /**
     * "Etat de validation de la fiche"
     */
    const STATE_VALIDATION_DRAFT = 'draft';
    const STATE_VALIDATION_PENDING = 'pending';
    const STATE_VALIDATION_VALIDATED = 'validated';

    /**
     * "Etat de validation des agendas des utilisateurs de la fiche"
     */
    const AGENDA_ALL_CONFIRMED    = 'all_agenda_confirmed';
    const AGENDA_PARTLY_CONFIRMED = 'agenda_partly_confirmed';
    const AGENDA_NONE_CONFIRMED   = 'no_agenda_confirmed';
    const AGENDA_NOT_CONCERNED    = 'agenda_not_concerned';

    const AGENDA_CONFIRMED_STATUS = [
        self::AGENDA_ALL_CONFIRMED,
        self::AGENDA_PARTLY_CONFIRMED,
        self::AGENDA_NONE_CONFIRMED,
        self::AGENDA_NOT_CONCERNED,
    ];

    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var ArrayCollection
     */
    private $participants;

    /**
     * @var array
     */
    private $registrationData;

    /**
     * @var array
     */
    private $data;

    /**
     * @var ArrayCollection
     */
    private $orders;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var DateTimeInterface
     */
    private $lastLoginAt;

    /**
     * "Etat de la fiche"
     *
     * @var string
     */
    private $state = self::STATE_PENDING;

    /**
     * "Etat de la validation de la fiche par l'utilisateur"
     *
     * @var string
     */
    private $validationState = self::STATE_VALIDATION_DRAFT;

    /**
     * @var int
     */
    private $completeness = 0;

    /**
     * @var bool
     */
    private $enable = true;

    /**
     * "Suivi commercial"
     *
     * @var Admin|null
     */
    private $follower;

    /**
     * @var User
     */
    private $owner;

    /**
     * @var bool
     */
    private $inCatalog = false;

    /**
     * @var DateTimeInterface
     */
    private $inCatalogAt;

    /** @var Spot|null */
    private $spot;

    /**
     * @var bool
     */
    private $imported = false;

    /**
     * @var null|Group
     */
    private $group;

    /**
     * The attendance of the sheet for the event (présence / Annule sa venue)
     *
     * @var bool
     */
    private $attend = true;

    /**
     * @var string|null
     */
    private $title = null;

    /** @var string */
    private $agendaConfirmedStatus = self::AGENDA_NOT_CONCERNED;

    /** @var string */
    private $phoneValidationStatus = ValidationStatus::NOT_CONCERNED;

    /** @var ArrayCollection */
    private $availableSlots;

    /** @var string */
    private $availabilityConfirmationStatus = ConfirmationStatus::NONE_CONFIRMED;

    /** @var string */
    private $commercialStatus = CommercialStatus::STATUS_NONE;

    /** @var null|DateTimeInterface */
    private $reminderDate;

    /** @var Sheet|null */
    private $duplicatedFrom;

    /** @var LinkedSheets|null */
    private $linkedSheets;

    /** @var Analytics|null */
    private $analytics;

    /**
     * Sheet constructor.
     *
     * @param Event             $event
     * @param Type              $type
     * @param array             $data
     * @param User              $owner
     * @param DateTimeInterface $createdAt
     * @param Group|null        $group
     */
    public function __construct(
        Event $event,
        Type $type,
        array $data,
        User $owner,
        DateTimeInterface $createdAt,
        ?Group $group = null
    ) {
        $this->event        = $event;
        $this->type         = $type;
        $this->data         = $data;
        $this->owner        = $owner;
        $this->createdAt    = $createdAt;
        $this->lastLoginAt  = $createdAt;
        $this->participants = new ArrayCollection();
        $this->orders       = new ArrayCollection();
        $this->state        = self::STATE_PENDING;
        $this->completeness = 0;
        $this->group        = $group;
        $this->availableSlots = new ArrayCollection();

        $this->analytics = new Analytics();
    }

    public function __tostring()
    {
        return $this->title.' '.$this->id.' '.$this->type->getIdentifier();
    }

    /**
     * @return array
     */
    public static function getAllStates()
    {
        return [
            self::STATE_ACCEPTED,
            self::STATE_PENDING,
            self::STATE_VALIDATED,
            self::STATE_REFUSED,
        ];
    }

    /**
     * @return array
     */
    public static function getAllValidationStates()
    {
        return [
            self::STATE_VALIDATION_DRAFT,
            self::STATE_VALIDATION_PENDING,
            self::STATE_VALIDATION_VALIDATED,
        ];
    }

    /**
     * @return int|float
     */
    public function getMaxParticipant()
    {
        return $this->type->getMaxParticipant();
    }

    /**
     * @return bool
     *
     * @deprecated This method is deprecated as the participants now also depend of the max quantity of the product
     */
    public function canBuyParticipant()
    {
        return $this->getMaxParticipant() > $this->countParticipants();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTraceableName()
    {
        return TraceableName::SHEET_TRACEABLE_NAME;
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * Get type.
     *
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    public function getTypeTitle(string $locale): string
    {
        return $this->type->getTitle($locale);
    }

    public function getCategoriesTitles(string $locale): string
    {
        return implode(', ', $this->type->getCategoriesTitles($locale));
    }

    /**
     * Get participants.
     *
     * @return ArrayCollection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @return Participant[]
     */
    public function getParticipantsArray(): array
    {
        return $this->participants->toArray();
    }

    /**
     * @return Participant[]
     */
    public function getLinkedSheetsParticipants(): array
    {
        if (!$this->hasLinkedSheets()) {
            throw new \LogicException(
                'This method can not be called when sheet has not LinkedSheets. Check before that Sheet::hasLinkedSheets() returns true.'
            );
        }

        $participants = [];

        foreach ($this->getLinkedSheets()->getSheets() as $linkedSheet) {
            foreach ($linkedSheet->getParticipantsArray() as $participant) {
                $participants[$participant->getId()] = $participant;
            }
        }

        return $participants;
    }

    /**
     * @throws SheetException
     *
     * @return Participant
     */
    public function getFirstParticipant(): Participant
    {
        $firstParticipant = $this->getParticipants()->first();

        if (false === $firstParticipant) {
            throw new SheetException('Sheet cannot have no participant');
        }

        return $firstParticipant;
    }

    /**
     * @deprecated use countParticipants()
     *
     * @return int
     */
    public function countParticipant(): int
    {
        return $this->participants->count();
    }

    /**
     * @param Participant $participant
     *
     * @return Sheet
     */
    public function addParticipant(Participant $participant)
    {
        $this->participants->add($participant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Sheet
     */
    public function removeParticipant(Participant $participant)
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data.
     *
     * @param array $data
     *
     * @return Sheet
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get Registration Data for the sheet
     *
     * @return array
     */
    public function getRegistrationData()
    {
        return $this->registrationData;
    }

    /**
     * Set Registration Data for the sheet
     *
     * @param array $registrationData
     *
     * @return Sheet
     */
    public function setRegistrationData($registrationData)
    {
        $this->registrationData = $registrationData;

        return $this;
    }

    /**
     * Get packageData.
     *
     * @deprecated
     *
     * @return array
     */
    public function getPackageData()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getValidationState()
    {
        return $this->validationState;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->getType()->getPackage();
    }

    /**
     * Get createdAt
     *
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Set lastLoginAt
     *
     * @param DateTimeInterface $lastLoginAt
     *
     * @return Sheet
     */
    public function setLastLoginAt(DateTimeInterface $lastLoginAt)
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    /**
     * Get lastLoginAt
     *
     * @return DateTimeInterface
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * Get type sheetTemplate.
     *
     * @return SheetTemplate
     */
    public function getTypeSheetTemplate()
    {
        return $this->getType()->getSheetTemplate();
    }

    /**
     * @param Order $order
     *
     * @return Sheet
     */
    public function addOrder(Order $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->orders->toArray();
    }

    /**
     * @return Order[]
     */
    public function getNotCancelledOrders()
    {
        return array_filter($this->getOrders(), function (Order $order) {
            return !$order->isCancelled();
        });
    }

    /**
     * Get the sheet user owner
     *
     * @return User
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getOwnerId(): ?int
    {
        return $this->owner->getId();
    }

    /**
     * Get the sheet participant owner
     *
     * @return Participant|null
     */
    public function getParticipantOwner()
    {
        return $this->getUserParticipant($this->owner);
    }

    /**
     * @param User $user
     *
     * @return Participant|null
     */
    public function getUserParticipant(User $user)
    {
        foreach ($this->participants as $participant) {
            // To avoid __isInitialized__: false
            if ($participant->getUser()->getId() === $user->getId()) {
                return $participant;
            }
        }

        return null;
    }

    public function getUserLocale(User $user): string
    {
        $participant = $this->getUserParticipant($user);

        return $participant ? $participant->getLocale() : $user->getLocale();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function hasUser(User $user)
    {
        return $this->owner->getId() === $user->getId() || $this->participants->exists(function ($index, Participant $participant) use ($user) {
            // To avoid __isInitialized__: false
            return $participant->getUser()->getId() === $user->getId();
        });
    }

    public function hasUserInLinkedSheets(User $user): bool
    {
        if (!$this->hasLinkedSheets()) {
            return false;
        }

        $linkedSheets = $this->getLinkedSheets()->getSheets();

        foreach ($linkedSheets as $linkedSheet) {
            if ($linkedSheet->hasUser($user)) {
                return true;
            }
        }

        return false;
    }

    public function hasLinkedSheet(Sheet $sheet): bool
    {
        if (!$this->hasLinkedSheets()) {
            return false;
        }

        return in_array($sheet, $this->getLinkedSheets()->getSheets(), true);
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasParticipant(Participant $participant)
    {
        return $this->participants->contains($participant);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function hasUserParticipant(User $user)
    {
        return null !== $this->getUserParticipant($user);
    }

    public function countParticipants(): int
    {
        return $this->participants->count();
    }

    public function hasOnlyOneParticipant(): int
    {
        return 1 === $this->countParticipants();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isOwner(User $user)
    {
        return $this->owner === $user;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $completeness
     *
     * @return Sheet
     */
    public function setCompleteness($completeness)
    {
        $this->completeness = $completeness;

        return $this;
    }

    /**
     * @param string $state
     *
     * @throws \InvalidArgumentException
     */
    public function setState(string $state): void
    {
        if (!\in_array($state, self::getAllStates(), true)) {
            throw new \InvalidArgumentException('Invalid argument $state; Must be one of Sheet\'s states');
        }

        $this->state = $state;
    }

    public function markAsValidated(): void
    {
        $this->state = self::STATE_VALIDATED;
    }

    public function markAsAccepted(): void
    {
        $this->state = self::STATE_ACCEPTED;
    }

    /**
     * @return bool
     */
    public function isValidated()
    {
        return self::STATE_VALIDATED === $this->state;
    }

    /**
     * @param bool $state
     *
     * @return Sheet
     */
    public function setEnable($state)
    {
        $this->enable = $state;

        return $this;
    }

    /**
     * @param string $validationState
     *
     * @return Sheet
     */
    public function setValidationState($validationState)
    {
        if (in_array($validationState, self::getAllValidationStates())) {
            $this->validationState = $validationState;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAccepted()
    {
        return self::STATE_ACCEPTED === $this->state;
    }

    /**
     * @return bool
     */
    public function isRefused()
    {
        return self::STATE_REFUSED === $this->state;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return 100 === $this->completeness;
    }

    /**
     * @return int
     */
    public function getCompleteness()
    {
        return $this->completeness;
    }

    public static function getCompletenessStatus(int $completeness)
    {
        if ($completeness < 40) {
            return 'danger';
        }

        if ($completeness < 100) {
            return 'warning';
        }

        if (100 === $completeness) {
            return 'success';
        }

        return 'danger';
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true === $this->enable;
    }

    /**
     * @return bool
     */
    public function isValidationPending()
    {
        return self::STATE_VALIDATION_PENDING === $this->validationState;
    }

    /**
     * @return bool
     */
    public function isValidationDraft()
    {
        return self::STATE_VALIDATION_DRAFT === $this->validationState;
    }

    /**
     * Get follower
     *
     * @return Admin|null
     */
    public function getFollower()
    {
        return $this->follower;
    }

    public function getFollowerName(): ?string
    {
        return $this->follower ? $this->follower->getDisplayName() : null;
    }

    /**
     * Assign follower
     *
     * @param Admin $follower
     *
     * @return Sheet
     */
    public function assign(Admin $follower)
    {
        if (!$follower->isOrganizer() && !$follower->isOperator()) {
            throw new SheetException('Follower must be an organizer or operator.');
        }

        $this->follower = $follower;

        return $this;
    }

    /**
     * Un-assign follower for the sheet
     *
     * @return Sheet
     */
    public function unAssign()
    {
        $this->follower = null;

        return $this;
    }

    /**
     * Get participants users + the owner
     *
     * @return User[]
     */
    public function getUsers()
    {
        /** @var ArrayCollection $users */
        $users = $this->participants->map(function (Participant $participant) {
            return $participant->getUser();
        });

        if (!$users->contains($this->owner)) {
            $users[] = $this->owner;
        }

        return $users->toArray();
    }

    /**
     * @return bool
     */
    public function hasOrders()
    {
        return count($this->getOrders()) > 0;
    }

    /**
     * @return bool
     */
    public function hasNotCancelledOrders()
    {
        return count($this->getNotCancelledOrders()) > 0;
    }

    /**
     * @deprecated use isInInternalCatalog
     * @return bool
     */
    public function isInCatalog()
    {
        return $this->isInInternalCatalog();
    }

    public function isInInternalCatalog(): bool
    {
        return $this->inCatalog;
    }

    public function isInExternalCatalog(): bool
    {
        return $this->isAccepted() || $this->isValidated();
    }

    public function isInExternalOrInternalCatalog(): bool
    {
        return $this->isInInternalCatalog() || $this->isInExternalCatalog();
    }

    /**
     * @param bool $inCatalog
     */
    public function setInCatalog($inCatalog)
    {
        $this->inCatalog = $inCatalog;
    }

    /**
     * @return DateTimeInterface
     */
    public function getInCatalogAt()
    {
        return $this->inCatalogAt;
    }

    /**
     * @param DateTimeInterface $inCatalogAt
     */
    public function setInCatalogAt($inCatalogAt)
    {
        $this->inCatalogAt = $inCatalogAt;
    }

    /**
     * @param Type $type
     */
    public function updateType($type)
    {
        if ($this->type === $type) {
            return;
        }

        $this->type = $type;

        // Set state to pending
        $this->state = self::STATE_PENDING;

        // Remove from catalog
        $this->setInCatalog(false);
    }

    /**
     * @return Spot|null
     */
    public function getSpot(): ?Spot
    {
        return $this->spot;
    }

    public function hasSpot(): bool
    {
        return null !== $this->spot;
    }

    /**
     * @param Spot $spot
     */
    public function setSpot(Spot $spot)
    {
        $this->spot = $spot;
    }

    /**
     * Unassign spot
     */
    public function removeSpot()
    {
        $this->spot = null;
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        return $this->imported;
    }

    /**
     * @param bool $imported
     *
     * @return $this
     */
    public function setImported($imported)
    {
        $this->imported = $imported;

        // Imported sheet don't have last login yet
        if ($imported) {
            $this->lastLoginAt = null;
        }

        return $this;
    }

    /**
     * @return Sheet
     */
    public function submitToValidation()
    {
        $this->validationState = self::STATE_VALIDATION_PENDING;

        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerLocale()
    {
        return $this->getOwner()->getLocale();
    }

    /**
     * @return null|Group
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function hasLinkedSheets(): bool
    {
        return null !== $this->linkedSheets;
    }

    public function getLinkedSheets(): ?LinkedSheets
    {
        return $this->linkedSheets;
    }

    public function getGroupTitle(): ?string
    {
        return $this->group ? $this->group->getTitle() : null;
    }

    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }

    public function setLinkedSheets(LinkedSheets $linkedSheets): self
    {
        $this->linkedSheets = $linkedSheets;

        return $this;
    }

    /**
     * Set Group to null
     */
    public function unassignFromGroup()
    {
        $this->group = null;
    }

    /**
     * @return bool
     */
    public function hasGroup()
    {
        return null !== $this->getGroup();
    }

    /**
     * Cancel/Confirm the attendance of the sheet for the vent
     *
     * @param bool $attendance
     */
    public function setAttendance($attendance)
    {
        $this->attend = $attendance;
    }

    /**
     * @return bool
     */
    public function attend()
    {
        return $this->attend;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     *
     * @return Sheet
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Status of the agenda confirmation
     *
     * @return string
     */
    public function getAgendaConfirmedStatus(): string
    {
        return $this->agendaConfirmedStatus;
    }

    /**
     * @param string $agendaConfirmedStatus
     */
    public function setAgendaConfirmedStatus(string $agendaConfirmedStatus)
    {
        $this->agendaConfirmedStatus = $agendaConfirmedStatus;
    }

    /**
     * @param string $phoneValidationStatus
     */
    public function setPhoneValidationStatus(string $phoneValidationStatus)
    {
        $this->phoneValidationStatus = $phoneValidationStatus;
    }

    /**
     * @param AvailableSlot[] $availableSlots
     */
    public function setAvailableSlots(array $availableSlots)
    {
        foreach ($availableSlots as $newAvailableSlot) {
            $found = false;

            foreach ($this->availableSlots as $oldAvailableSlot) {
                if ($newAvailableSlot->getSlot()->getId() === $oldAvailableSlot->getSlot()->getId()) {
                    $found = true;

                    break;
                }
            }

            if (false === $found) {
                $this->availableSlots->add($newAvailableSlot);
            }
        }

        foreach ($this->availableSlots as $key => $oldAvailableSlot) {
            $found = false;

            foreach ($availableSlots as $newAvailableSlot) {
                if ($newAvailableSlot->getSlot()->getId() === $oldAvailableSlot->getSlot()->getId()) {
                    $found = true;

                    break;
                }
            }

            if (false === $found) {
                $this->availableSlots->remove($key);
            }
        }
    }

    /**
     * @return array
     */
    public function getAvailableSlots(): array
    {
        return $this->availableSlots->toArray();
    }

    /**
     * @return string
     */
    public function getPhoneValidationStatus(): string
    {
        return $this->phoneValidationStatus;
    }

    /**
     * @return string
     */
    public function getAvailabilityConfirmationStatus(): string
    {
        return $this->availabilityConfirmationStatus;
    }

    /**
     * @param string $availabilityConfirmationStatus
     */
    public function setAvailabilityConfirmationStatus(string $availabilityConfirmationStatus): void
    {
        $this->availabilityConfirmationStatus = $availabilityConfirmationStatus;
    }

    /**
     * @return string
     */
    public function getCommercialStatus(): string
    {
        return $this->commercialStatus;
    }

    /**
     * @param string $commercialStatus
     */
    public function setCommercialStatus(string $commercialStatus): void
    {
        $this->commercialStatus = $commercialStatus;
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
     * @return DateTimeInterface|null
     */
    public function getReminderDate(): ?DateTimeInterface
    {
        return $this->reminderDate;
    }

    /**
     * @param DateTimeInterface|null $reminderDate
     */
    public function setReminderDate($reminderDate): void
    {
        $this->reminderDate = $reminderDate;
    }

    public function getDuplicatedFrom(): ?Sheet
    {
        return $this->duplicatedFrom;
    }

    public function setDuplicatedFrom(Sheet $sheet): void
    {
        $this->duplicatedFrom = $sheet;
    }

    public static function duplicateSheetFrom(
        Sheet $sheet,
        ?Group $group,
        Type $type,
        \DateTimeInterface $createdAt
    ): Sheet {
        $duplicatedSheet = new self(
            $type->getEvent(),
            $type,
            $sheet->getData(),
            $sheet->getOwner(),
            $createdAt
        );

        if ($group instanceof Group) {
            $duplicatedSheet->setGroup($group);
        }

        $duplicatedSheet->setImported(true);
        $duplicatedSheet->setTitle($sheet->getTitle());
        $duplicatedSheet->setRegistrationData($sheet->getRegistrationData());
        $duplicatedSheet->setDuplicatedFrom($sheet);

        foreach ($sheet->getParticipants() as $participant) {
            $duplicatedSheet->addParticipant(Participant::duplicateFrom($participant, $duplicatedSheet, $createdAt));
        }

        return $duplicatedSheet;
    }

    public function changeOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Analytics
     */
    public function getAnalytics(): Analytics
    {
        if (null === $this->analytics) {
            $this->analytics = new Analytics();
        }

        return $this->analytics;
    }
}
