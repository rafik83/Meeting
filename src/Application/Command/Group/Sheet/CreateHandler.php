<?php

namespace Proximum\Vimeet\Application\Command\Group\Sheet;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Group\Sheet\SheetCreatedByManagerEvent;
use Proximum\Vimeet\Application\Event\Package\MustSelectPackageEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
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
    private $dateTime;

    /** @var SheetInfoSetter */
    private $sheetInfoSetter;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoSetter $sheetInfoSetter,
        ParticipantRepositoryInterface $participantRepository,
        DelayedEventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->sheetInfoSetter       = $sheetInfoSetter;
        $this->participantRepository = $participantRepository;
        $this->eventDispatcher       = $eventDispatcher;
        $this->dateTime              = $dateTime;
    }

    public function handle(Create $command): void
    {
        $originalSheet = $command->sheet;

        // The new sheet should not be in catalog and the state should be draft
        $newSheet = new Sheet(
            $originalSheet->getEvent(),
            $originalSheet->getType(),
            $originalSheet->getData(),
            $originalSheet->getOwner(),
            $this->dateTime,
            $originalSheet->getGroup()
        );
        $newSheet->setTitle($command->title);
        $newSheet->setRegistrationData($originalSheet->getRegistrationData());

        // Set the spot of the original sheet to the new one
        if (null !== $originalSheet->getSpot()) {
            $newSheet->setSpot($originalSheet->getSpot());
        }

        // Set the follower of the original sheet to the new one
        if (null !== $originalSheet->getFollower()) {
            $newSheet->assign($originalSheet->getFollower());
        }

        $this->sheetInfoSetter->setSheetTitle($newSheet, $command->title);

        $this->sheetRepository->add($newSheet);

        /** @var Participant[] $originalParticipants */
        $originalParticipants = $originalSheet->getParticipants()->toArray();

        foreach ($originalParticipants as $participant) {
            $newParticipant = new Participant(
                $newSheet,
                $participant->getUser(),
                $participant->getData(),
                true,
                $this->dateTime
            );
            $newParticipant->setRegistrationComplete($participant->isRegistrationComplete());
            $newParticipant->setRegistrationStep($participant->getRegistrationStep());
            $newSheet->addParticipant($newParticipant);

            $this->participantRepository->add($newParticipant);
        }

        // Send Sheet Update Event to calculate completeness of the sheet
        $sheetUpdatedEvent = new SheetUpdatedEvent($newSheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);

        $mustSelectPackageEvent = new MustSelectPackageEvent($newSheet);
        $this->eventDispatcher->dispatch(Events::MUST_SELECT_PACKAGE, $mustSelectPackageEvent);

        $this->eventDispatcher->dispatch(
            Events::SHEET_CREATE_BY_GROUP_MANAGER,
            new SheetCreatedByManagerEvent($newSheet, $this->dateTime)
        );
    }
}
