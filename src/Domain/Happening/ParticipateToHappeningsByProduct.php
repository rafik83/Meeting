<?php

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipation;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipationHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\HappeningParticipationAutomaticallyUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ParticipantWithAttributedProductUpdated;
use Proximum\Vimeet\Domain\View\Happening\HappeningParticipationView;

class ParticipateToHappeningsByProduct
{
    /** @var HappeningsNotOverlapped */
    private $happeningsNotOverlapped;

    /** @var ParticipantWithAttributedProductUpdated */
    private $participantWithAttributedProductUpdated;

    /** @var ParticipateToHappeningWithProductToBuyChecker */
    private $participateToHappeningWithProductToBuyChecker;

    /** @var UpdateParticipationHandler */
    private $updateParticipationHandler;
    /** @var HappeningsWithProductsBySheetPackageGetter */
    private $happeningsWithProductsBySheetPackageGetter;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        HappeningsWithProductsBySheetPackageGetter $happeningsWithProductsBySheetPackageGetter,
        HappeningsNotOverlapped $happeningsNotOverlapped,
        ParticipantWithAttributedProductUpdated $participantWithAttributedProductUpdated,
        ParticipateToHappeningWithProductToBuyChecker $participateToHappeningWithProductToBuyChecker,
        UpdateParticipationHandler $updateParticipationHandler,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->happeningsNotOverlapped = $happeningsNotOverlapped;
        $this->participantWithAttributedProductUpdated = $participantWithAttributedProductUpdated;
        $this->participateToHappeningWithProductToBuyChecker = $participateToHappeningWithProductToBuyChecker;
        $this->updateParticipationHandler = $updateParticipationHandler;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->happeningsWithProductsBySheetPackageGetter = $happeningsWithProductsBySheetPackageGetter;
    }

    public function handle(Sheet $sheet): void
    {
        $concernedHappeningsWithProducts = $this->happeningsWithProductsBySheetPackageGetter->get($sheet);

        if (empty($concernedHappeningsWithProducts)) {
            return;
        }

        $sheetParticipants = $sheet->getParticipantsArray();

        // Process only participants with attributed product updated (added or removed)
        $participantsWithAttributedProductUpdated = $this
            ->participantWithAttributedProductUpdated
            ->getFilteredByParticipants($sheetParticipants)
        ;

        $availableHappeningsByParticipantId = $this->getAvailableHappeningsByParticipant(
            $concernedHappeningsWithProducts,
            $participantsWithAttributedProductUpdated
        );

        $participantsByHappeningId = $this
            ->getParticipantsByHappening(
                $participantsWithAttributedProductUpdated,
                $availableHappeningsByParticipantId
            );

        $happeningParticipationViewByHappening = [];
        foreach ($concernedHappeningsWithProducts as $happeningId => $happening) {
            $happeningParticipationViewByHappening[] = $this->updateParticipationHandler->handle(
                new UpdateParticipation($happening, $sheet, $participantsByHappeningId[$happeningId] ?? [])
            );
        }

        if (empty($happeningParticipationViewByHappening)) {
            return;
        }

        $happeningParticipationViewByHappeningNotEmpty = array_filter(
            $happeningParticipationViewByHappening,
            function (HappeningParticipationView $happeningParticipationView) {
                return !empty($happeningParticipationView->addedParticipants) ||
                    !empty($happeningParticipationView->removedParticipants);
            }
        );

        if (empty($happeningParticipationViewByHappeningNotEmpty)) {
            return;
        }

        $this->delayedEventDispatcher->dispatch(
            Events::HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED,
            new HappeningParticipationAutomaticallyUpdatedEvent($happeningParticipationViewByHappeningNotEmpty, $sheet)
        );
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
}
