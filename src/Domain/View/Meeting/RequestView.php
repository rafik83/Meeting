<?php

namespace Proximum\Vimeet\Domain\View\Meeting;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class RequestView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $sheetNameFrom;

    /**
     * @var int
     */
    public $sheetFromId;

    /**
     * @var string
     */
    public $sheetNameTo;

    /**
     * @var int
     */
    public $sheetToId;

    /**
     * @var string
     */
    private $state;

    /**
     * @var bool
     */
    public $hasMeeting;

    /**
     * @var ArrayCollection
     */
    public $fromParticipants;

    /**
     * @var ArrayCollection
     */
    public $toParticipants;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var string
     */
    public $message;

    /**
     * @var bool
     */
    public $canSee = false;

    /**
     * @var bool
     */
    public $canEdit = false;

    /**
     * @var bool
     */
    public $canCancel = false;

    /**
     * @var bool
     */
    public $canRefuse = false;

    /**
     * @var bool
     */
    public $canApprove = false;

    /**
     * @var \DateTimeInterface
     */
    public $stateUpdatedAt;

    /**
     * @param int                $id
     * @param int                $sheetFromId
     * @param string             $sheetNameFrom
     * @param int                $sheetToId
     * @param string             $sheetNameTo
     * @param string             $state
     * @param \DateTimeInterface $createdAt
     * @param \DateTimeInterface $stateUpdatedAt
     * @param string             $message
     * @param bool               $hasMeeting
     */
    public function __construct(
        $id,
        $sheetFromId,
        $sheetNameFrom,
        $sheetToId,
        $sheetNameTo,
        $state,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $stateUpdatedAt,
        $message,
        bool $hasMeeting = false
    ) {
        $this->id               = $id;
        $this->sheetFromId      = $sheetFromId;
        $this->sheetNameFrom    = $sheetNameFrom;
        $this->sheetToId        = $sheetToId;
        $this->sheetNameTo      = $sheetNameTo;
        $this->state            = $state;
        $this->createdAt        = $createdAt;
        $this->stateUpdatedAt   = $stateUpdatedAt;
        $this->fromParticipants = new ArrayCollection();
        $this->toParticipants   = new ArrayCollection();
        $this->message          = $message;
        $this->hasMeeting       = $hasMeeting;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        if (true === $this->hasMeeting) {
            return Request::STATE_PLANNED;
        }

        return $this->state;
    }
}
