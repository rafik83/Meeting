<?php

namespace Proximum\Vimeet\Application\Exception\Happening;

class NotEnoughtRemainingParticipationsException extends HappeningException
{
    /** @var int */
    private $remainingParticipations;

    /**
     * @param int        $remainingParticipations
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($remainingParticipations, $code = 0, \Exception $previous = null)
    {
        parent::__construct('Not enought remaining participations for this happening', $code, $previous);

        $this->remainingParticipations = $remainingParticipations;
    }

    /**
     * @return int
     */
    public function getRemainingParticipations()
    {
        return $this->remainingParticipations;
    }
}
