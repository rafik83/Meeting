<?php

namespace Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\RegistrationCloseDateAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RegistrationCloseDateAccessCheckerTest extends TestCase
{
    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
    }

    public function testAllowedAccess()
    {
        $dateTime = new \DateTime('2017-01-03 10:10');

        $this->event->getConfiguration()->setDates(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            new \DateTime('2017-01-05 10:10')
        );

        $registrationOpenDateAccessChecker = new RegistrationCloseDateAccessChecker($dateTime);

        $this->assertEquals(true, $registrationOpenDateAccessChecker->allowedToAccess($this->event));
    }

    public function testNotAllowedAccess()
    {
        $dateTime = new \DateTime('2017-01-02 10:10');

        $this->event->getConfiguration()->setDates(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            new \DateTime('2017-01-01 10:10')
        );

        $registrationOpenDateAccessChecker = new RegistrationCloseDateAccessChecker($dateTime);

        $this->assertEquals(false, $registrationOpenDateAccessChecker->allowedToAccess($this->event));
    }

    public function testAllowedAccessWithNullDate()
    {
        $dateTime = new \DateTime('2017-01-02 10:10');
        $registrationOpenDateAccessChecker = new RegistrationCloseDateAccessChecker($dateTime);

        $this->assertEquals(true, $registrationOpenDateAccessChecker->allowedToAccess($this->event));
    }
}
