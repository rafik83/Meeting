<?php

namespace Proximum\Vimeet\Application\Command\Event\Day;

use Proximum\Vimeet\Application\Exception\Slot\SlotOutOfDayException;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class UpdateHandler
{
    /** @var DayRepositoryInterface */
    private $dayRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /**
     * @param DayRepositoryInterface         $dayRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->dayRepository = $dayRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $slots = $this->meetingSlotRepository->findByEvent($update->event);

        $this->verifyNoSlotOutOfDay($update->days, $slots);

        $this->dayRepository->removeFromEvent($update->event);

        foreach ($update->days as $day) {
            /** @var \DateTimeInterface $eventStartTime */
            $eventStartTime = $day['startTime'];

            /** @var \DateTimeInterface $eventEndTime */
            $eventEndTime = $day['endTime'];

            $this->dayRepository->add(
                new Day(
                    $update->event,
                    $eventStartTime,
                    $eventEndTime
                )
            );
        }
    }

    /**
     * @param array         $days
     * @param MeetingSlot[] $slots
     *
     * @throws SlotOutOfDayException
     */
    private function verifyNoSlotOutOfDay(array $days, array $slots)
    {
        foreach ($slots as $slot) {
            $slotOutOfDay = true;

            foreach ($days as $day) {
                // if we determines that the slot is in the day, we break and pass to the next slot
                if ($slot->getBegin() >= $day['startTime'] && $slot->getEnd() <= $day['endTime']) {
                    $slotOutOfDay = false;

                    break;
                }
            }

            if (true === $slotOutOfDay) {
                throw new SlotOutOfDayException($slot);
            }
        }
    }
}
