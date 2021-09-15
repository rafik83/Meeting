<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\RegistrationAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\RegistrationCloseDateAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\RegistrationOpenDateAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RegistrationAccessCheckerTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var RegistrationAccessChecker */
    private $registrationAccessChecker;

    /** @var RegistrationOpenDateAccessChecker */
    private $registrationOpenDateAccessChecker;

    /** @var RegistrationCloseDateAccessChecker */
    private $registrationCloseDateAccessChecker;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $this->dateTime =  new \DateTime('2017-01-10 10:10');
        $this->registrationOpenDateAccessChecker = $this->prophesize(RegistrationOpenDateAccessChecker::class);
        $this->registrationCloseDateAccessChecker = $this->prophesize(RegistrationCloseDateAccessChecker::class);

        $this->registrationAccessChecker = new RegistrationAccessChecker(
            $this->dateTime,
            $this->registrationOpenDateAccessChecker->reveal(),
            $this->registrationCloseDateAccessChecker->reveal()
        );
    }

    public function testAllowedToAccessWithGoodDates()
    {
        $this->registrationOpenDateAccessChecker->allowedToAccess($this->event)->shouldBeCalled()->willReturn(true);
        $this->registrationCloseDateAccessChecker->allowedToAccess($this->event)->shouldBeCalled()->willReturn(true);

        $this->assertEquals(
            RegistrationAccessChecker::REGISTRATION_OPEN,
            $this->registrationAccessChecker->getRegistrationAccessStatus($this->event)
        );
    }

    public function testNotAllowedAccessWithOpenDateNotReached()
    {
        $this->registrationOpenDateAccessChecker->allowedToAccess($this->event)->shouldBeCalled()->willReturn(false);
        $this->registrationCloseDateAccessChecker->allowedToAccess($this->event)->shouldNotBeCalled();

        $this->assertEquals(
            RegistrationAccessChecker::REGISTRATION_NOT_OPEN,
            $this->registrationAccessChecker->getRegistrationAccessStatus($this->event)
        );
    }

    public function testNotAllowedAccessWithCloseDateReached()
    {
        $this->registrationOpenDateAccessChecker->allowedToAccess($this->event)->shouldBeCalled()->willReturn(true);
        $this->registrationCloseDateAccessChecker->allowedToAccess($this->event)->shouldBeCalled()->willReturn(false);

        $this->assertEquals(
            RegistrationAccessChecker::REGISTRATION_CLOSED,
            $this->registrationAccessChecker->getRegistrationAccessStatus($this->event)
        );
    }
}
