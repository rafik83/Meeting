<?php

namespace Proximum\Vimeet\Application\Exception\MeetingRequest\Import;

use Proximum\Vimeet\Application\Exception\MeetingRequest\MeetingRequestException;

class MultipleParticipantsFoundException extends MeetingRequestException
{
    private string $email;

    public function __construct(string $email) {
        parent::__construct();
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
