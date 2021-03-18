<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class HasUserSheetStateChangedQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var array */
    public $data;

    public function __construct(Event $event, User $user, array $data)
    {
        $this->event = $event;
        $this->data = $data;
        $this->user = $user;
    }
}
