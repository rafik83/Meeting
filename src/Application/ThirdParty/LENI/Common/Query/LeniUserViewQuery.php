<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

class LeniUserViewQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var null|ExtraData */
    public $previousExtraData;

    /**
     * @param Event          $event
     * @param User           $user
     * @param null|ExtraData $previousExtraData
     */
    public function __construct(Event $event, User $user, ?ExtraData $previousExtraData)
    {
        $this->event = $event;
        $this->user = $user;
        $this->previousExtraData = $previousExtraData;
    }
}
