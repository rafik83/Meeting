<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotManager
{
    const INTERVAL = 5;
    const DURATION = 10;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SlotGenerator */
    private $slotGenerator;

    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository, SlotGenerator $slotGenerator)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->slotGenerator = $slotGenerator;
    }

    public function create(Event $event, int $quantity, ?\DateTimeInterface $start = null)
    {
        $interval = self::INTERVAL;
        $duration = self::DURATION;

        if (null === $start) {
            $start = new \DateTime();
        }
        $begin = new \DateTime(sprintf('%s %s', $start->format('Y-m-d'), '08:00:00'));
        $end = clone $begin;
        $end->add(new \DateInterval(sprintf('PT%sM', ($interval + $duration) * $quantity)));

        $slots = $this->slotGenerator->generate(
            $event,
            [new Recipe($begin, $end, $interval, $duration)]
        );

        foreach ($slots as $slot) {
            $this->meetingSlotRepository->add($slot);
        }
    }

    public function addSlot(MeetingSlot $slot): void
    {
        $this->meetingSlotRepository->add($slot);
    }

    /**
     * @return MeetingSlot[]
     */
    public function findByEvent(Event $event): array
    {
        return $this->meetingSlotRepository->findByEvent($event);
    }

    /**
     * @return null|MeetingSlot
     */
    public function findByEventAndId(Event $event, int $slotId): ?MeetingSlot
    {
        return $this->meetingSlotRepository->find($event, $slotId);
    }
}
