<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Create
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var SheetView[] */
    public $sheetViews;

    /** @var string */
    public $title;

    /** @var bool */
    public $forceSheetTitle = false;

    /**
     * @param Event $event
     * @param User  $user
     */
    public function __construct(Event $event, User $user)
    {
        $this->event      = $event;
        $this->user       = $user;
        $this->sheetViews = [];
    }
}
