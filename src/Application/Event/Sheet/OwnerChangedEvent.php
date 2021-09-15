<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher;

class OwnerChangedEvent extends EventDispatcher\Event
{
    /** @var Sheet */
    public $sheet;

    /** @var Admin */
    public $admin;

    /** @var User */
    public $previousOwner;

    /** @var string */
    public $comment;

    public function __construct(
        Sheet $sheet,
        Admin $admin,
        User $previousOwner,
        string $comment
    ) {
        $this->sheet = $sheet;
        $this->admin = $admin;
        $this->previousOwner = $previousOwner;
        $this->comment = $comment;
    }
}
