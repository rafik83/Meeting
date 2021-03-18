<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class FilterAvailableSlotAndSpecificSlotCheckerHandler
{
    /** @var DDayGuesser */
    private $dDayGuesser;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /**
     * @param DDayGuesser                    $dDayGuesser
     * @param QueryBusInterface              $queryBus
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(
        DDayGuesser $dDayGuesser,
        QueryBusInterface $queryBus,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->dDayGuesser = $dDayGuesser;
        $this->queryBus = $queryBus;
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param FilterAvailableSlotAndSpecificSlotChecker $query
     *
     * @return FilterAvailableSlotAndSpecificSlotCheckerView
     */
    public function handle(FilterAvailableSlotAndSpecificSlotChecker $query): FilterAvailableSlotAndSpecificSlotCheckerView
    {
        $dDay = $this->dDayGuesser->isItDDayAndFeatureEnabled($query->event);
        $isUserParticipant = $query->sheet->hasUserParticipant($query->user);
        $filterAvailableSlot = $dDay && $isUserParticipant;
        $specificSlot = null;

        if (true === $filterAvailableSlot) {
            /** @var AvailableSlotView[] $availableSlots */
            $availableSlots = $this->queryBus->handle(
                new AvailableSlotsByParticipantQuery($query->event, $query->sheet->getUserParticipant($query->user))
            );

            $filterAvailableSlot = !empty($availableSlots);

            if (null !== $query->slotId) {
                $slot = $this->meetingSlotRepository->findById($query->slotId);

                if (null !== $slot) {
                    foreach ($availableSlots as $availableSlot) {
                        if ($availableSlot->id === $slot->getId()) {
                            $specificSlot = $slot;
                        }
                    }
                }
            }
        }

        return new FilterAvailableSlotAndSpecificSlotCheckerView(
            $filterAvailableSlot,
            $specificSlot
        );
    }
}
