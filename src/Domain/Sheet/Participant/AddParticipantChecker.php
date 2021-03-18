<?php

namespace Proximum\Vimeet\Domain\Sheet\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;

class AddParticipantChecker
{
    /**
     * @param Sheet $sheet
     *
     * @throws \DomainException
     *
     * @return bool
     */
    public function canAddParticipant(Sheet $sheet): bool
    {
        $package = $sheet->getType()->getPackage();

        if (null === $package) {
            throw new \DomainException('The package should not be null');
        }

        $numberOfParticipant = $sheet->countParticipants();

        if (!$package->isParticipantAndPlanningEnabled()) {
            if (null === $package->getMaxParticipant()) {
                return true;
            }

            return $numberOfParticipant < $package->getMaxParticipant();
        }

        if (null !== $package->getMaxParticipant() && $numberOfParticipant >= $package->getMaxParticipant()) {
            return false;
        }

        $participantProducts = $package->getParticipants();

        $quantity = 0;

        foreach ($participantProducts as $participantProduct) {
            if (INF === $participantProduct->getQuantityMax()) {
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
