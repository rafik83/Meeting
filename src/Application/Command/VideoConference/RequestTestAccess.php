<?php

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class RequestTestAccess implements Command
{
    /** @var string */
    public $sessionId;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    public function __construct(
        Event $event,
        string $sessionId,
        string $locale
    ) {
        $this->sessionId = $sessionId;
        $this->event = $event;
        $this->locale = $locale;
    }
}
