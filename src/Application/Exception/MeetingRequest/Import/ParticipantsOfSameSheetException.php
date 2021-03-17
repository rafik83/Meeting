<?php

namespace Proximum\Vimeet\Application\Exception\MeetingRequest\Import;

use Proximum\Vimeet\Application\Exception\MeetingRequest\MeetingRequestException;

class ParticipantsOfSameSheetException extends MeetingRequestException
{
    private string $emailFrom;
    private string $emailTo;

    public function __construct(string $emailFrom, string $emailTo) {
        parent::__construct();
        $this->emailFrom = $emailFrom;
        $this->emailTo = $emailTo;
    }

    public function getEmailFrom(): string
    {
        return $this->emailFrom;
    }

    public function getEmailTo(): string
    {
        return $this->emailTo;
    }
}
