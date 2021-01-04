<?php

namespace Proximum\Vimeet\Application\Command\Participant\Export;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class Export implements Command
{
    /** @var Event */
    public $event;

    /** @var array */
    public $participantIds;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    public function __construct(
        Event $event,
        array $participantIds,
        Admin $admin,
        string $locale
    ) {
        $this->event = $event;
        $this->participantIds = $participantIds;
        $this->admin = $admin;
        $this->locale = $locale;
    }
}
