<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant\NotifyParticipantAdded;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant\NotifyParticipantAddedHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant\OnParticipantAdded;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant\OnParticipantAddedHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User\Create;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class OnParticipantAddedHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $createHandler;

    /** @var ObjectProphecy */
    private $notifyParticipantAddedHandler;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $participant;

    public function setUp()
    {
        $this->createHandler = $this->prophesize(CreateHandler::class);
        $this->notifyParticipantAddedHandler = $this->prophesize(NotifyParticipantAddedHandler::class);
        $this->event = $this->prophesize(Event::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->participant->getEmail()->willReturn('email@example.net');
        $this->participant->getLocale()->willReturn('fr');
    }

    public function testHandleCreated()
    {
        $this->createHandler
            ->handle(new Create($this->event->reveal(), 'email@example.net', 'fr'))
            ->shouldBeCalled()
            ->willReturn(CreateHandler::RESPONSE_CREATED)
        ;

        $this->notifyParticipantAddedHandler->handle(Argument::any())->shouldNotBeCalled();

        $handler = new OnParticipantAddedHandler(
            $this->createHandler->reveal(),
            $this->notifyParticipantAddedHandler->reveal()
        );

        $handler->handle(new OnParticipantAdded($this->event->reveal(), $this->participant->reveal()));
    }

    public function testHandle()
    {
        $this->createHandler
            ->handle(new Create($this->event->reveal(), 'email@example.net', 'fr'))
            ->shouldBeCalled()
            ->willReturn(CreateHandler::RESPONSE_ALREADY_CREATED)
        ;

        $this->notifyParticipantAddedHandler
            ->handle(new NotifyParticipantAdded($this->event->reveal(), $this->participant->reveal(), 'fr'))
            ->shouldBeCalled()
        ;

        $handler = new OnParticipantAddedHandler(
            $this->createHandler->reveal(),
            $this->notifyParticipantAddedHandler->reveal()
        );

        $handler->handle(new OnParticipantAdded($this->event->reveal(), $this->participant->reveal()));
    }
}
