<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;

class SendNotification
{
    /** @var Version */
    public $currentVersion;

    /** @var array */
    public $diff;

    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    /**
     * @param Event   $event
     * @param Sheet   $sheet
     * @param User    $user
     * @param Version $currentVersion
     * @param array   $diff
     */
    public function __construct(Event $event, Sheet $sheet, User $user, Version $currentVersion, array $diff)
    {
        $this->currentVersion = $currentVersion;
        $this->diff = $diff;
        $this->event = $event;
        $this->user = $user;
        $this->sheet = $sheet;
    }
}
