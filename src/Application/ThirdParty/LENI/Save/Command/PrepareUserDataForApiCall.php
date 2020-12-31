<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

class PrepareUserDataForApiCall implements Command
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /** @var ExtraData|null */
    public $previousUserEventExtraData;

    public function __construct(Event $event, User $user, ?ExtraData $previousUserEventExtraData)
    {
        $this->user = $user;
        $this->event = $event;
        $this->previousUserEventExtraData = $previousUserEventExtraData;
    }
}
