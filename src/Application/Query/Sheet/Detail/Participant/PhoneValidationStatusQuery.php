<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class PhoneValidationStatusQuery
{
    /** @var Participant */
    public $participant;

    /**
     * @param Participant $participant
     */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }
}
