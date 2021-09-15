<?php

namespace Proximum\Vimeet\Application\Command\Scan\Happening;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class ScanHappening implements Command
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Happening */
    public $happening;

    /** @var \DateTimeInterface */
    public $scannedAt;

    public function __construct(
        Event $event,
        User $user,
        Happening $happening,
        \DateTimeInterface $scannedAt
    ) {
        $this->event = $event;
        $this->user = $user;
        $this->happening = $happening;
        $this->scannedAt = $scannedAt;
    }
}
