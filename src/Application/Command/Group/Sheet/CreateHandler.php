<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Group\Sheet;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\SheetInfoSetter;

class CreateHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var SheetInfoSetter */
    private $sheetInfoSetter;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param SheetInfoSetter                $sheetInfoSetter
     * @param ParticipantRepositoryInterface $participantRepository
     * @param \DateTimeInterface             $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoSetter $sheetInfoSetter,
        ParticipantRepositoryInterface $participantRepository,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->sheetInfoSetter       = $sheetInfoSetter;
        $this->participantRepository = $participantRepository;
        $this->datetime              = $datetime;
    }

    /**
     * @param Create $command
     */
    public function handle(Create $command)
    {
        $originalSheet = $command->sheet;

        $newSheet = new Sheet(
            $originalSheet->getEvent(),
            $originalSheet->getType(),
            $originalSheet->getData(),
            $originalSheet->getOwner(),
            $this->datetime,
            $originalSheet->getGroup()
        );
        $newSheet->setRegistrationData($originalSheet->getRegistrationData());

        // Set the spot of the original sheet to the new one
        if (null !== $originalSheet->getSpot()) {
            $newSheet->setSpot($originalSheet->getSpot());
        }

        $newSheet->setAttendance($originalSheet->attend());

        // Set the follower of the original sheet to the new one
        if (null !== $originalSheet->getFollower()) {
            $newSheet->assign($originalSheet->getFollower());
        }

        // The new sheet should not be in catalog and the state should be draft

        $this->sheetInfoSetter->setSheetTitle($newSheet, $command->title);

        $this->sheetRepository->add($newSheet);

        /** @var Participant[] $originalParticipants */
        $originalParticipants = $originalSheet->getParticipants()->toArray();

        foreach ($originalParticipants as $participant) {
            $newParticipant = new Participant($newSheet, $participant->getUser(), $participant->getData(), true);
            $newParticipant->setVisio($participant->isVisio());
            $newParticipant->setRegistrationComplete($participant->isRegistrationComplete());
            $newParticipant->setRegistrationStep($participant->getRegistrationStep());

            $this->participantRepository->add($newParticipant);
        }
    }
}
