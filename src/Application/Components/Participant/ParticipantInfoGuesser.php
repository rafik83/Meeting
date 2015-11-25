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
            if ($field['type'] === 'lib_last_name') {
                if (isset($participantData[$fieldKey])) {
                    return $participantData[$fieldKey];
                }
            }
        }
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
            if ($field['type'] === 'lib_first_name') {
                if (isset($participantData[$fieldKey])) {
                    return $participantData[$fieldKey];
                }
            }
        }
    }

    /**
     * @param Participant $participant
     *
     * @return string
     */
    public function guessParticipantInfo(Participant $participant)
    {
        return $this->guessParticipantLastName($participant) . ' ' . $this->guessParticipantFirstName($participant);
    }
}
