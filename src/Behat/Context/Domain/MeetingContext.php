<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\MeetingContextProxyInterface;

class MeetingContext implements Context
{
    /** @var MeetingContextProxyInterface */
    private $meetingContextProxy;

    /**
     * @param MeetingContextProxyInterface $meetingContextProxy
     */
    public function __construct(MeetingContextProxyInterface $meetingContextProxy)
    {
        $this->meetingContextProxy = $meetingContextProxy;
    }

    /**
     * @Given /^there is a meeting on spot "(?P<spotReference>[^"]+)"$/
     *
     * @param string $spotReference
     */
    public function thereIsAMeetingOnSpot($spotReference)
    {
        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $meetingManager = $this->meetingContextProxy->getMeetingManager();

        $meetingManager->createMeetingOnSpot($event, $spotReference);
    }
}
