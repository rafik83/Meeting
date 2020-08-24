<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\SpotContextProxyInterface;

class SpotContext implements Context
{
    /** @var SpotContextProxyInterface */
    private $spotContextProxy;

    /**
     * @param SpotContextProxyInterface $spotContextProxy
     */
    public function __construct(SpotContextProxyInterface $spotContextProxy)
    {
        $this->spotContextProxy = $spotContextProxy;
    }

    /**
     * @Given /^there is an (?P<active>(in)?active) spot "(?P<reference>[^"]+)" with size of (?P<size>\d+), meeting capacity of (?P<meetingCapacity>\d+), seat capacity of (?P<seatCapacity>\d+)$/
     */
    public function thereIsAnActiveSpot(string $active, string $reference, int $size, int $meetingCapacity, int $seatCapacity)
    {
        $event = $this->spotContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $this->spotContextProxy->getSpotManager()->create($event, $reference, $size, $meetingCapacity, $seatCapacity, $active === 'active');
    }

    /**
     * @Given /^spot "(?P<spotReference>[^"]+)" is assigned to this sheet$/
     *
     * @param string $spotReference
     */
    public function spotIsAssignedToThisSheet($spotReference)
    {
        $event = $this->spotContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $sheet = $this->spotContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $this->spotContextProxy->getSpotManager()->assignToSheet($event, $sheet, $spotReference);
    }
}
