<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SendEmailingByType implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $messageId;

    /** @var Sheet[] */
    public $sheets;

    public function __construct(Event $event, string $messageId, array $sheets)
    {
        $this->event = $event;
        $this->messageId = $messageId;
        $this->sheets = $sheets;
    }
}
