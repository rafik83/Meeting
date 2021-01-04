<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\Request;

class CreateForm
{
    /** @var Request */
    public $request;

    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $actionUrl;

    /** @var string */
    public $timezone;

    public function __construct(
        Request $request,
        Event $event,
        Sheet $sheet,
        User $user,
        string $actionUrl,
        string $timezone
    ) {
        $this->request = $request;
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->actionUrl = $actionUrl;
        $this->timezone = $timezone;
    }
}
