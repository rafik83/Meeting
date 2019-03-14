<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantOfSheetWithPackageParticipantAndPlanningDisabled
{
    /** @var ParticipantProductSetter */
    private $participantProductSetter;

    public function __construct(ParticipantProductSetter $participantProductSetter)
    {
        $this->participantProductSetter = $participantProductSetter;
    }

    public function handleParticipant(Participant $participant): void
    {
        $package = $participant->getSheet()->getPackage();

        if (false !== $package->isParticipantAndPlanningEnabled()) {
            return;
        }

        $productParticipant = $package->getFirstProductParticipant();
        $this->participantProductSetter->setProductOnParticipant($participant, $productParticipant);
    }
}
