<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Participant;

use Proximum\Vimeet\Application\Components\Sheet\TaggedInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantInfoGuesser
{
    /**
     * @var TaggedInfoGuesser
     */
    private $taggedInfoGuesser;

    /**
     * ParticipantInfoGuesser constructor.
     *
     * @param TaggedInfoGuesser $taggedInfoGuesser
     */
    public function __construct(TaggedInfoGuesser $taggedInfoGuesser)
    {
        $this->taggedInfoGuesser = $taggedInfoGuesser;
    }

    /**
     * @param Participant $participant
     *
     * @return string
     */
    public function guessParticipantLastName(Participant $participant)
    {
        $template = $participant->getSheet()->getType()->getParticipantTemplate();
        $data     = $participant->getData();

        return $this->taggedInfoGuesser->guessFirst($template, $data, Tag::PARTICIPANT_FIRSTNAME);
    }

    /**
     * @param Participant $participant
     *
     * @return string
     */
    public function guessParticipantFirstName(Participant $participant)
    {
        $template = $participant->getSheet()->getType()->getParticipantTemplate();
        $data     = $participant->getData();

        return $this->taggedInfoGuesser->guessFirst($template, $data, Tag::PARTICIPANT_LASTNAME);
    }

    /**
     * @param Participant $participant
     *
     * @return string
     */
    public function guessParticipantInfo(Participant $participant)
    {
        $string = trim($this->guessParticipantLastName($participant) . ' ' . $this->guessParticipantFirstName($participant));

        return empty($string) ? sprintf('#%d', $participant->getId()) : $string;
    }
}
