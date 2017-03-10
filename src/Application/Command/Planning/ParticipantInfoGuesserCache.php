<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantInfoGuesserCache
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $guesser;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param ParticipantInfoGuesser $guesser
     */
    public function __construct(ParticipantInfoGuesser $guesser)
    {
        $this->guesser = $guesser;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return string
     */
    public function guessParticipantLastName(Participant $participant, $locale)
    {
        $key = $participant->getId() . $locale;

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->guesser->guessParticipantLastName($participant, $locale);
        }

        return $this->cache[$key];
    }
}
