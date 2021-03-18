<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Event;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Event\DayContextProxyInterface;

class DayContext implements Context
{
    /** @var DayContextProxyInterface */
    private $dayContextProxy;

    /**
     * @param DayContextProxyInterface $dayContextProxy
     */
    public function __construct(DayContextProxyInterface $dayContextProxy)
    {
        $this->dayContextProxy = $dayContextProxy;
    }

    /**
     * @Given /^this event occurs today from "(?P<begin>[^"]+)" to "(?P<end>[^"]+)"$/
     *
     * @param string $begin must be a string like "08:00"
     * @param string $end
     */
    public function thisEventOccursTodayFromBeginToEnd(string $begin, string $end)
    {
        $event = $this->dayContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $now = new \DateTime();
        $begin = new \DateTime(sprintf('%s %s:00', $now->format('Y-m-d'), $begin));
        $end = new \DateTime(sprintf('%s %s:00', $now->format('Y-m-d'), $end));

        $day = $this->dayContextProxy->getDayManager()->create($event, $begin, $end);
        $this->dayContextProxy->getStorage()->set('day', $day);
    }

    /**
     * @Given /^this event occurs the "(?P<day>[^"]+)" from "(?P<begin>[^"]+)" to "(?P<end>[^"]+)"$/
     *
     * @param string $day
     * @param string $begin
     * @param string $end
     */
    public function thisEventOccursTheDateFromBeginToEnd(string $day, string $begin, string $end)
    {
        $event = $this->dayContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $begin = new \DateTime(sprintf('%s %s:00', $day, $begin));
        $end = new \DateTime(sprintf('%s %s:00', $day, $end));

        $eventDay = $this->dayContextProxy->getDayManager()->create($event, $begin, $end);
        $this->dayContextProxy->getStorage()->set('day', $eventDay);
    }
}
