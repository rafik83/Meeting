<?php

namespace Proximum\Vimeet\Domain\Participant;

class GetParticipantInitials
{
    public function __invoke($firstName, $lastName): string
    {
        return sprintf(
            '%s%s',
            strtoupper(mb_substr($firstName, 0, 1)),
            strtoupper(mb_substr($lastName, 0, 1))
        );
    }
}
