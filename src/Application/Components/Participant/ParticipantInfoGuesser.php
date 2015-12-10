<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantInfoGuesser
{
    /**
     * @param Participant $participant
     *
     * @return string
     */
    public function guessParticipantLastName(Participant $participant)
    {
        $participantTemplate = $participant->getSheet()->getType()->getParticipantTemplate();
        $participantData     = $participant->getData();

        foreach ($participantTemplate as $fieldKey => $field) {
            if ($field['type'] === 'lib_last_name' && isset($participantData[$fieldKey])) {
                return $participantData[$fieldKey];
            }
        }

        return '';
    }

    /**
     * @param Participant $participant
     *
     * @return string
     */
    public function guessParticipantFirstName(Participant $participant)
    {
        $participantTemplate = $participant->getSheet()->getType()->getParticipantTemplate();
        $participantData     = $participant->getData();

        foreach ($participantTemplate as $fieldKey => $field) {
            if ($field['type'] === 'lib_first_name' && isset($participantData[$fieldKey])) {
                return $participantData[$fieldKey];
            }
        }

        return '';
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
