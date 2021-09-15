<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\GeneratedEvent;
use Proximum\Vimeet\Application\Exception\Slot\SlotOutOfDayException;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class GenerateHandler
{
    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SlotGenerator */
    private $slotGenerator;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * GenerateHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface  $meetingSlotRepository
     * @param SlotGenerator                   $slotGenerator
     * @param DelayedEventDispatcherInterface $delayedEventDispatcher
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SlotGenerator $slotGenerator,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->slotGenerator         = $slotGenerator;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Generate $generate
     *
     * @return GenerateResult
     */
    public function handle(Generate $generate)
    {
        $slots = $this->slotGenerator->generate($generate->event, $generate->recipes);
        $days = $generate->event->getDays();

        foreach ($slots as $slot) {
            $this->verifySlotNotOutOfDay($days, $slot);

            $this->meetingSlotRepository->add($slot);
        }

        $this->delayedEventDispatcher->dispatch(Events::SLOT_GENERATED, new GeneratedEvent($generate->event));

        return new GenerateResult(count($slots));
    }

    /**
     * @param Day[]       $days
     * @param MeetingSlot $slot
     *
     * @throws SlotOutOfDayException
     */
    private function verifySlotNotOutOfDay(array $days, MeetingSlot $slot): void
    {
        foreach ($days as $day) {
            if ($slot->getBegin() >= $day->getStartTime() && $slot->getEnd() <= $day->getEndTime()) {
                return;
            }
        }

        throw new SlotOutOfDayException($slot);
    }
}
