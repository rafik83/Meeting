<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class AvailabilityConfirmationChecker
{
    const ORIGIN_CATALOG = 'availability_confirmation_from_catalog';
    const ORIGIN_MEETING_REQUEST_MANAGEMENT = 'availability_confirmation_from_meeting_request_management';

    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $origin;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $origin
     */
    public function __construct(Event $event, Sheet $sheet, User $user, string $origin)
    {
        $this->event  = $event;
        $this->sheet  = $sheet;
        $this->user   = $user;
        $this->origin = $origin;
    }
}
