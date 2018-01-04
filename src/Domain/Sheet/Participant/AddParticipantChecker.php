<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;

class AddParticipantChecker
{
    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function canAddParticipant(Sheet $sheet)
    {
        $package = $sheet->getType()->getPackage();

        if ($package === null) {
            throw new \DomainException('The package should not be null');
        }

        $numberOfParticipant = $sheet->countParticipants();

        if (null !== $package && $numberOfParticipant >= $package->getMaxParticipant()) {
            return false;
        }

        $participantProducts = $package->getParticipants();

        $quantity = 0;

        foreach ($participantProducts as $participantProduct) {
            if ($participantProduct->getQuantityMax() === INF) {
                return true;
            }

            $quantity += $participantProduct->getQuantityMax();

            if ($numberOfParticipant < $quantity) {
                return true;
            }
        }

        if ($numberOfParticipant >= $quantity) {
            return false;
        }

        return true;
    }
}
