<?php

namespace Proximum\Vimeet\Application\Components\Happening\Participation;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class DisableEnableParticipation
{
    /** @var HappeningParticipationRepositoryInterface */
    private $participationRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param HappeningParticipationRepositoryInterface $participationRepository
     * @param SheetRepositoryInterface                  $sheetRepository
     * @param JobQueueInterface                         $jobQueue
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $participationRepository,
        SheetRepositoryInterface $sheetRepository,
        JobQueueInterface $jobQueue
    ) {
        $this->participationRepository = $participationRepository;
        $this->sheetRepository = $sheetRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * This methods disable / enable participation of happening for Type that have access
     *
     * @param Happening $happening
     */
    public function resolveParticipations(Happening $happening)
    {
        $types = $happening->getTypes();
        $participations = $this->participationRepository->findByHappening($happening);

        $sheetsImpacted = [];
        foreach ($participations as $participation) {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent(
                $participation->getUser(),
                $happening->getEvent()
            );

            foreach ($this->resolveParticipation($participation, $sheets, $types) as $sheetId => $sheetImpacted) {
                $sheetsImpacted[$sheetId] = $sheetImpacted;
            }
        }

        foreach ($sheetsImpacted as $sheet) {
            $this->jobQueue->aggregateSheetAvailableSlot($sheet);
        }
    }

    /**
     * @param Event $event
     * @param User  $user
     */
    public function resolveParticipationsForUser(Event $event, User $user)
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        $participations = $this->participationRepository->findByUser($user, $event, false);

        $sheetsImpacted = [];

        foreach ($participations as $participation) {
            $happening = $participation->getHappening();
            $types = $happening->getTypes();

            foreach ($this->resolveParticipation($participation, $sheets, $types) as $sheetId => $sheetImpacted) {
                $sheetsImpacted[$sheetId] = $sheetImpacted;
            }
        }

        foreach ($sheetsImpacted as $sheet) {
            $this->jobQueue->aggregateSheetAvailableSlot($sheet);
        }
    }

    /**
     * @param HappeningParticipation $participation
     * @param Sheet[]                $sheets
     * @param Type[]                 $happeningTypes
     *
     * @return array
     */
    private function resolveParticipation(
        HappeningParticipation $participation,
        array $sheets,
        array $happeningTypes
    ) {
        $participationPreviousState = $participation->isDisabled();
        $typeFound = false;

        foreach ($sheets as $sheet) {
            if ($sheet->attend() && in_array($sheet->getType(), $happeningTypes, true)) {
                $participation->setDisabled(false);
                $typeFound = true;

                break;
            }
        }

        if (false === $typeFound) {
            $participation->setDisabled(true);
        }

        $sheetImpacted = [];

        if ($participationPreviousState !== $participation->isDisabled()) {
            $this->participationRepository->update($participation);

            foreach ($sheets as $sheet) {
                $sheetImpacted[$sheet->getId()] = $sheet;
            }
        }

        return $sheetImpacted;
    }
}
