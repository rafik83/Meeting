<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Happening;

use Throwable;

class MaxNumberHappeningParticipationReachedException extends HappeningException
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param string $fullNameOfParticipantReached
     */

    private $fullNameOfParticipantReached;

    public function __construct(string $fullNameOfParticipantReached, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->fullNameOfParticipantReached = $fullNameOfParticipantReached;
    }

    public function getfullNameOfParticipantReached(): string
    {
        return $this->fullNameOfParticipantReached;
    }
}

