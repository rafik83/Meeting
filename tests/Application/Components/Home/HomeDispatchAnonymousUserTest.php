<?php

namespace Application\Components\Home;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;
use Proximum\Vimeet\Application\View\Home\HomeDispatchAnonymousView;
use Proximum\Vimeet\Domain\KeyDates\Checker\RegistrationAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class HomeDispatchAnonymousUserTest extends TestCase
{
    /** @var ObjectProphecy */
    private $registrationAccessChecker;

    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->registrationAccessChecker = $this->prophesize(RegistrationAccessChecker::class);
        $this->event = EventFactory::createEvent();
    }

    public function testHandleWithRegistrationNotOpen()
    {
        $expectedView = new HomeDispatchAnonymousView(RegistrationAccessChecker::REGISTRATION_NOT_OPEN);

        $this->registrationAccessChecker
            ->getRegistrationAccessStatus($this->event)
            ->shouldBeCalled()
            ->willReturn(RegistrationAccessChecker::REGISTRATION_NOT_OPEN);

        $handler = new HomeDispatchAnonymousUser($this->registrationAccessChecker->reveal());

        $resultView = $handler->handle($this->event);

        $this->assertEquals($expectedView, $resultView);
    }

    public function testHandleWithRegistrationClosed()
    {
        $expectedView = new HomeDispatchAnonymousView(RegistrationAccessChecker::REGISTRATION_CLOSED);

        $this->registrationAccessChecker
            ->getRegistrationAccessStatus($this->event)
            ->shouldBeCalled()
            ->willReturn(RegistrationAccessChecker::REGISTRATION_CLOSED);

        $handler = new HomeDispatchAnonymousUser($this->registrationAccessChecker->reveal());

        $resultView = $handler->handle($this->event);

        $this->assertEquals($expectedView, $resultView);
    }

    public function testHandleWithRegistrationOpen()
    {
        $this->registrationAccessChecker
            ->getRegistrationAccessStatus($this->event)
            ->shouldBeCalled()
            ->willReturn(RegistrationAccessChecker::REGISTRATION_OPEN);

        $handler = new HomeDispatchAnonymousUser($this->registrationAccessChecker->reveal());

        $resultView = $handler->handle($this->event);

        $this->assertEquals(null, $resultView);
    }
}
