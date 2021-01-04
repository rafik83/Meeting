<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\Exception\Planner\SlotNotConfiguredException;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $slotRepository;

    /**
     * @param MeetingSlotRepositoryInterface $slotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $slotRepository)
    {
        $this->slotRepository = $slotRepository;
    }

    /**
     * @param SlotViewQuery $query
     *
     * @throws SlotNotConfiguredException
     *
     * @return SlotView[]
     */
    public function handle(SlotViewQuery $query)
    {
        $slotViews = [];

        $slots = $this->slotRepository->getAvailableSlotByEvent($query->event);

        $index = 0;

        foreach ($slots as $slot) {
            $day = $this->getCorrespondingDay($query, $slot);

            if (null !== $day) {
                $slotViews[] = new SlotView(
                    $slot->getId(),
                    $index,
                    $slot->getBegin()->format('G'), // 24-hour format of an hour without leading zeros
                    (int) $slot->getBegin()->format('i'), // to remove leading zero
                    $day
                );

                ++$index;
            }
        }

        // In case of slot nof configured and of slot out of the days
        if (empty($slots) || empty($slotViews)) {
            throw new SlotNotConfiguredException();
        }

        return $slotViews;
    }

    /**
     * @param SlotViewQuery $query
     * @param MeetingSlot   $slot
     *
     * @return null|Day
     */
    public function getCorrespondingDay(SlotViewQuery $query, MeetingSlot $slot): ?Day
    {
        foreach ($query->days as $day) {
            if ((int) $slot->getBegin()->format('d') === $day->day
                && (int) $slot->getBegin()->format('m') === $day->month
                && (int) $slot->getBegin()->format('Y') === $day->year
            ) {
                return $day;
            }
        }

        return null;
    }
}
