<?php

namespace Proximum\Vimeet\Application\View\Participant;

use Proximum\Vimeet\Domain\Participant\GetParticipantInitials;

class CardView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var bool
     */
    public $editable;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $position;

    /**
     * @var string
     */
    public $avatar;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @var int
     */
    public $sheetId;

    /**
     * @var string
     */
    public $initials;

    /** @var bool */
    public $getCheckinStatus;

    /** @var bool */
    public $isCheckedToday;

    public ?bool $isOnline;

    public ?int $toUserId;

    /**
     * @param int    $id
     * @param bool   $editable
     * @param string $firstname
     * @param string $lastname
     * @param string $position
     * @param string $avatar
     * @param bool   $owner
     * @param int    $sheetId
     * @param bool   $getCheckinStatus
     * @param bool   $isCheckedToday
     */
    public function __construct(
        $id,
        $editable,
        $firstname,
        $lastname,
        $position,
        $avatar,
        $owner,
        $sheetId,
        bool $getCheckinStatus = false,
        bool $isCheckedToday = false,
        ?bool $isOnline = null,
        ?int $toUserId = null
    ) {
        $this->id = $id;
        $this->editable = $editable;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->position = $position;
        $this->avatar = $avatar;
        $this->owner = $owner;
        $this->sheetId = $sheetId;
        $this->initials = (new GetParticipantInitials())($firstname, $lastname);
        $this->getCheckinStatus = $getCheckinStatus;
        $this->isCheckedToday = $isCheckedToday;
        $this->isOnline = $isOnline;
        $this->toUserId = $toUserId;
    }
}
