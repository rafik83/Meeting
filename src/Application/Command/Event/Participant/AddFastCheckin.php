<?php

namespace Proximum\Vimeet\Application\Command\Event\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class AddFastCheckin implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $email;

    /** @var string */
    public $firstname;

    /** @var string */
    public $lastname;

    /** @var string */
    public $mobile;

    /** @var string */
    public $sheetTitle;

    /** @var Type */
    public $type;

    /** @var string */
    public $country;

    public function __construct(Event $event, string $email)
    {
        $this->event = $event;
        $this->email = $email;
    }
}
