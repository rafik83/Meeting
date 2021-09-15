<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Unavailability;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Unavailability\MassContextProxyInterface;

class MassContext implements Context
{
    /** @var MassContextProxyInterface */
    private $massContextProxy;

    public function __construct(MassContextProxyInterface $massContextProxy)
    {
        $this->massContextProxy = $massContextProxy;
    }

    /**
     * @Given /^there is a mass unavailability "(?P<name>[^"]+)" for this type the (?P<day>\d{4}-\d{2}-\d{2}) from (?P<from>\d{1,2}:\d{2}) to (?P<to>\d{1,2}:\d{2})$/
     */
    public function thereIsAMassUnavailabilityForThisType($name, $day, $from, $to)
    {
        $event = $this->massContextProxy->getStorage()->get('event');
        $type = $this->massContextProxy->getStorage()->get('type');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (null === $type) {
            throw new \InvalidArgumentException('Missing Type');
        }

        $begin = new \DateTime(sprintf('%s %s:00', $day, $from));
        $end = new \DateTime(sprintf('%s %s:00', $day, $to));

        $mass = $this->massContextProxy->getMassManager()->create($event, null, $name, $begin, $end, $type);

        $this->massContextProxy->getStorage()->set('massUnavailability', $mass);
    }

    /**
     * @Given this slot is assigned to this mass unavailability for this user
     */
    public function massUnavailabilitiesAreDispatched()
    {
        $mass = $this->massContextProxy->getStorage()->get('massUnavailability');
        $meetingSlot = $this->massContextProxy->getStorage()->get('meetingSlot');
        $user = $this->massContextProxy->getStorage()->get('user');

        if (null === $mass) {
            throw new \InvalidArgumentException('Missing Mass unavailability');
        }

        if (null === $meetingSlot) {
            throw new \InvalidArgumentException('Missing Meeting slot');
        }

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        $this->massContextProxy->getMassManager()->assignSlotToMass($meetingSlot, $user, $mass);

    }
}
