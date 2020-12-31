<?php

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class AvailableSlotsByParticipantQueryHandler
{
    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DDayGuesser */
    private $dDayGuesser;

    /**
     * @param DDayGuesser                    $dDayGuesser
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        DDayGuesser $dDayGuesser,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->dDayGuesser           = $dDayGuesser;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->dateTime              = $dateTime;
    }

    /**
     * @param AvailableSlotsByParticipantQuery $query
     *
     * @return AvailableSlotView[]
     */
    public function handle(AvailableSlotsByParticipantQuery $query): array
    {
        if (!$this->dDayGuesser->isItDDayAndFeatureEnabled($query->event)) {
            return [];
        }

        $availableSlots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $query->event,
            [$query->participant]
        );

        $availableSlotViews = [];

        $datePlus10Minutes = clone $this->dateTime;
        $datePlus10Minutes->add(new \DateInterval('PT10M'));

        foreach ($availableSlots as $availableSlot) {
            if ($availableSlot->getBegin() >= $datePlus10Minutes) {
                $availableSlotViews[] = new AvailableSlotView(
                    $availableSlot->getId(),
                    $availableSlot->getBegin(),
                    $availableSlot->getEnd()
                );
            }
        }

        return $availableSlotViews;
    }
}
