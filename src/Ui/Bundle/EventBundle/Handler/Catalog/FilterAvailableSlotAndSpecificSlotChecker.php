<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class FilterAvailableSlotAndSpecificSlotChecker
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var int|null */
    public $slotId;

    /** @var Event */
    public $event;

    /**
     * @param Event    $event
     * @param Sheet    $sheet
     * @param User     $user
     * @param int|null $slotId
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        int $slotId = null
    ) {
        $this->sheet = $sheet;
        $this->user = $user;
        $this->slotId = $slotId;
        $this->event = $event;
    }
}
