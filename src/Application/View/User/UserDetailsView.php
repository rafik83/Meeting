<?php

namespace Proximum\Vimeet\Application\View\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserDetailsView
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var User
     */
    public $user;

    /**
     * @var UserSheetView[]
     */
    public $userSheetView;

    /**
     * @param Event           $event
     * @param User            $user
     * @param UserSheetView[] $userSheetView
     */
    public function __construct(Event $event, User $user, array $userSheetView)
    {
        $this->event         = $event;
        $this->user          = $user;
        $this->userSheetView = $userSheetView;
    }
}
