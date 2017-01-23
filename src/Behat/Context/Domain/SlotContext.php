<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Service\Storage;
use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotContext implements Context
{
    /** @var Storage */
    private $storage;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SlotGenerator */
    private $slotGenerator;

    /**
     * @param Storage                        $storage
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param SlotGenerator                  $slotGenerator
     */
    public function __construct(
        Storage $storage,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SlotGenerator $slotGenerator
    ) {
        $this->storage = $storage;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->slotGenerator = $slotGenerator;
    }

    /**
     * @Given /^there are|is (?P<quantity>\d+) slot|slots in this event$/
     *
     * @param int $quantity
     */
    public function createSlots($quantity)
    {
        if (!$this->storage->getLastEvent()) {
            throw new \InvalidArgumentException('Missing event');
        }

        $interval = 5;
        $duration = 10;

        $now = new \DateTime();
        $begin = new \DateTime(sprintf('%s %s', $now->format('Y-m-d'), '08:00:00'));
        $end = clone $begin;
        $end->add(new \DateInterval(sprintf('PT%sM', $interval * $duration * $quantity)));

        $slots = $this->slotGenerator->generate(
            $this->storage->getLastEvent(),
            [new Recipe($begin, $end, $interval, $duration)]
        );

        foreach ($slots as $slot) {
            $this->meetingSlotRepository->add($slot);
        }
    }
}
