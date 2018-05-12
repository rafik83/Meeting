<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Application\Command\Happening\UpdateParticipation;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipationHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ParticipantWithAttributedProductUpdated;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class ParticipateToHappeningsByProduct
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var HappeningsNotOverlapped */
    private $happeningsNotOverlapped;

    /** @var ParticipantWithAttributedProductUpdated */
    private $participantWithAttributedProductUpdated;

    /** @var ParticipateToHappeningWithProductToBuyChecker */
    private $participateToHappeningWithProductToBuyChecker;

    /** @var UpdateParticipationHandler */
    private $updateParticipationHandler;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        HappeningsNotOverlapped $happeningsNotOverlapped,
        ParticipantWithAttributedProductUpdated $participantWithAttributedProductUpdated,
        ParticipateToHappeningWithProductToBuyChecker $participateToHappeningWithProductToBuyChecker,
        UpdateParticipationHandler $updateParticipationHandler
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->happeningsNotOverlapped = $happeningsNotOverlapped;
        $this->participantWithAttributedProductUpdated = $participantWithAttributedProductUpdated;
        $this->participateToHappeningWithProductToBuyChecker = $participateToHappeningWithProductToBuyChecker;
        $this->updateParticipationHandler = $updateParticipationHandler;
    }

    public function handle(Sheet $sheet): void
    {
        $sheetParticipants = $sheet->getParticipantsArray();

        $participantsWithAttributedProductUpdated = $this
            ->participantWithAttributedProductUpdated
            ->getFilteredByParticipants($sheetParticipants)
        ;

        $happeningsWithProducts = $this->happeningRepository->findWithProducts($sheet->getEvent());

        $availableHappeningsByParticipantId = $this->getAvailableHappeningsByParticipant(
            $happeningsWithProducts,
            $participantsWithAttributedProductUpdated
        );

        $participantsByHappeningId = $this
            ->getParticipantsByHappening(
                $participantsWithAttributedProductUpdated,
                $availableHappeningsByParticipantId
            );

        $happeningsById = $this->getHappeningsById($happeningsWithProducts);

        foreach ($participantsByHappeningId as $happeningId => $participants) {
            $this->updateParticipationHandler->handle(
                new UpdateParticipation($happeningsById[$happeningId], $sheet, $participants)
            );
        }
    }

    /**
     * @param Happening[]   $happenings
     * @param Participant[] $participants
     *
     * @return array
     */
    private function getAvailableHappeningsByParticipant(array $happenings, array $participants): array
    {
        $availableHappeningsByParticipantId = [];

        foreach ($participants as $participant) {
            $availableHappenings = array_values(
                array_filter(
                    $happenings,
                    function (Happening $happening) use ($participant) {
                        return $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                            $participant,
                            $happening
                        );
                    }
                )
            );

            $availableHappeningsByParticipantId[$participant->getId()] = $this
                ->happeningsNotOverlapped
                ->getHappeningsNotOverlapped($availableHappenings);
        }

        return $availableHappeningsByParticipantId;
    }

    /**
     * @param Participant[] $participants
     * @param array         $availableHappeningsByParticipantId
     *
     * @return array
     */
    private function getParticipantsByHappening(array $participants, array $availableHappeningsByParticipantId): array
    {
        $participantsByHappening = [];

        $participantsById = $this->getParticipantsById($participants);

        foreach ($availableHappeningsByParticipantId as $participantId => $happenings) {
            /** @var Happening[] $happenings */
            foreach ($happenings as $happening) {
                if (!isset($participantsByHappening[$happening->getId()])) {
                    $participantsByHappening[$happening->getId()] = [];
                }

                $participantsByHappening[$happening->getId()][] = $participantsById[$participantId];
            }
        }

        return $participantsByHappening;
    }

    /**
     * @param Participant[] $participants
     *
     * @return Participant[]
     */
    private function getParticipantsById(array $participants): array
    {
        $participantsById = [];

        foreach ($participants as $participant) {
            $participantsById[$participant->getId()] = $participant;
        }

        return $participantsById;
    }

    /**
     * @param Happening[] $happenings
     *
     * @return Happening[]
     */
    private function getHappeningsById(array $happenings): array
    {
        $happeningsById = [];

        foreach ($happenings as $happening) {
            $happeningsById[$happening->getId()] = $happening;
        }

        return $happeningsById;
    }
}
