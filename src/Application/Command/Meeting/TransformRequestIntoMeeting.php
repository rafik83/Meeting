<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class TransformRequestIntoMeeting implements Command
{
    /** @var Request */
    public $request;

    /** @var Event */
    public $event;

    /** @var string */
    public $createdBy;

    /** @var bool */
    public $blockedSpot;

    /** @var bool */
    public $blockedSlot;

    public function __construct(
        Request $request,
        string $createdBy,
        bool $blockedSpot = false,
        bool $blockedSlot = false
    ) {
        if (!in_array($createdBy, Meeting::CREATED_BY, true)) {
            throw new \InvalidArgumentException('$createdBy is not valid');
        }

        $this->request = $request;
        $this->event = $request->getEvent();
        $this->createdBy = $createdBy;
        $this->blockedSpot = $blockedSpot;
        $this->blockedSlot = $blockedSlot;
    }
}
