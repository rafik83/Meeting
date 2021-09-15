<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Command\Command;

class StatusChangeCallback implements Command
{
    /** @var string */
    public $status;

    /** @var string */
    public $archiveId;

    /** @var string|null */
    public $url;

    /** @var string */
    public $sessionId;

    public function __construct(
        string $archiveId,
        string $sessionId,
        string $status,
        ?string $url
    ) {
        $this->status = $status;
        $this->archiveId = $archiveId;
        $this->url = $url;
        $this->sessionId = $sessionId;
    }
}
