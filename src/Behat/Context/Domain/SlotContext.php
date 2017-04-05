<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\SlotContextProxyInterface;

class SlotContext implements Context
{
    /** @var SlotContextProxyInterface */
    private $slotContextProxy;

    /**
     * @param SlotContextProxyInterface $slotContextProxy
     */
    public function __construct(SlotContextProxyInterface $slotContextProxy)
    {
        $this->slotContextProxy = $slotContextProxy;
    }

    /**
     * @Given /^there (is|are) (?P<quantity>\d+) (slot|slots) in this event$/
     *
     * @param int $quantity
     */
    public function thereAreSlots($quantity)
    {
        $event = $this->slotContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $this->slotContextProxy->getSlotManager()->create($event, $quantity);
    }
}
