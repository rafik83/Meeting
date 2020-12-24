<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class SendingRequestData
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var array */
    public $data;

    /** @var bool */
    public $hasUserSheetStateChangedToValidated;

    public function __construct(Event $event, User $user, array $data, bool $hasUserSheetStateChangedToValidated)
    {
        $this->event = $event;
        $this->data = $data;
        $this->user = $user;
        $this->hasUserSheetStateChangedToValidated = $hasUserSheetStateChangedToValidated;
    }
}
