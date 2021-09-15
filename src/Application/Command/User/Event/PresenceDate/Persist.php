<?php

namespace Proximum\Vimeet\Application\Command\User\Event\PresenceDate;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\Block;

class Persist implements Command
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Block */
    public $block;

    public function __construct(Event $event, User $user, Block $block)
    {
        $this->event = $event;
        $this->user = $user;
        $this->block = $block;
    }
}
