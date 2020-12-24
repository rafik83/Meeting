<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\AvailabilityTimeRangeContextProxyInterface;

class AvailabilityTimeRangeContext implements Context
{
    /** @var AvailabilityTimeRangeContextProxyInterface */
    private $availabilityTimeRangeContextProxy;

    /**
     * @param AvailabilityTimeRangeContextProxyInterface $availabilityTimeRangeContextProxy
     */
    public function __construct(AvailabilityTimeRangeContextProxyInterface $availabilityTimeRangeContextProxy)
    {
        $this->availabilityTimeRangeContextProxy = $availabilityTimeRangeContextProxy;
    }

    /**
     * @Given /^The availability time range named "(?P<name>[^"]+)" which starts at "(?P<begin>[^"]+)" and ends at "(?P<end>[^"]+)" is created$/
     *
     * @param string $name
     * @param string $begin
     * @param string $end
     */
    public function createAvailabilityTimeRange(string $name = 'Plage de détente', string $begin, string $end): void
    {
        $event = $this->availabilityTimeRangeContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing event');
        }

        $availabilityTimeRange = $this->availabilityTimeRangeContextProxy->getAvailabilityTimeRangeManager()->create($event, $name, $begin, $end);

        $this->availabilityTimeRangeContextProxy->getStorage()->set('availabilityTimeRange', $availabilityTimeRange);
    }
}
