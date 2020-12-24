<?php

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantOfSheetWithPackageParticipantAndPlanningDisabled
{
    /** @var ParticipantProductSetter */
    private $participantProductSetter;

    public function __construct(ParticipantProductSetter $participantProductSetter)
    {
        $this->participantProductSetter = $participantProductSetter;
    }

    public function handle(Participant $participant): bool
    {
        $package = $participant->getSheet()->getPackage();

        if ($package->isParticipantAndPlanningEnabled()) {
            return false;
        }

        $productParticipant = $package->getFirstProductParticipant();
        $this->participantProductSetter->setProductOnParticipant($participant, $productParticipant);

        return true;
    }
}
