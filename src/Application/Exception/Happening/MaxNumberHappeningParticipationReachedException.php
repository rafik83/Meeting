<?php

namespace Proximum\Vimeet\Application\Exception\Happening;

use Proximum\Vimeet\Domain\Model\Participant;
use Throwable;

class MaxNumberHappeningParticipationReachedException extends HappeningException
{
    /**
     * @var Participant $participant
     */
    private $participant;

    /**
     * @param Participant $participant
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(Participant $participant, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->participant = $participant;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }
}
