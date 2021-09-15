<?php

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class AvailableSlotsByParticipantAndDayQueryHandler
{
    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DDayGuesser */
    private $dDayGuesser;

    /** @var MeetingRequestAccessChecker */
    private $meetingRequestAccessChecker;

    public function __construct(
        DDayGuesser $dDayGuesser,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        \DateTimeInterface $dateTime,
        MeetingRequestAccessChecker $meetingRequestAccessChecker
    ) {
        $this->dDayGuesser = $dDayGuesser;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->dateTime = $dateTime;
        $this->meetingRequestAccessChecker = $meetingRequestAccessChecker;
    }

    /**
     * @return AvailableSlotView[]
     */
    public function handle(AvailableSlotsByParticipantAndDayQuery $query): array
    {
        if (!$this->dDayGuesser->isItDDayAndFeatureEnabled($query->event)) {
            return [];
        }

        if (!$this->meetingRequestAccessChecker->allowedToAccess($query->event)) {
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
            if ($availableSlot->getBegin() >= $datePlus10Minutes
                && $query->day->getBegin() <= $availableSlot->getBegin()
                && $query->day->getEnd() >= $availableSlot->getEnd()
            ) {
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
