<?php

namespace Proximum\Vimeet\Tests\Domain\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class IsParticipantVisioTest extends TestCase
{
    public function testParticipantIsVisio(): void
    {
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();

        $extraData = $this->prophesize(User\Event\ExtraData::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getEvent()->willReturn($event);
        $participant->getUser()->willReturn($user);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository->getExtraDataForEventNameAndUser($event, Type::IS_PARTICIPANT_VISIO, $user)
            ->shouldBeCalled()
            ->willReturn($extraData->reveal());

        $isParticipantVisio = new IsParticipantVisio($extraDataRepository->reveal());

        $this->assertTrue($isParticipantVisio->isSatisfiedBy($participant->reveal()));
    }

    public function testParticipantIsNotVisio(): void
    {
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();

        $participant = $this->prophesize(Participant::class);
        $participant->getEvent()->willReturn($event);
        $participant->getUser()->willReturn($user);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository->getExtraDataForEventNameAndUser($event, Type::IS_PARTICIPANT_VISIO, $user)
            ->shouldBeCalled()
            ->willReturn(null);

        $isParticipantVisio = new IsParticipantVisio($extraDataRepository->reveal());

        $this->assertFalse($isParticipantVisio->isSatisfiedBy($participant->reveal()));
    }

    public function testEventIsVisio(): void
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setVisio(true);

        $participant = $this->prophesize(Participant::class);
        $participant->getEvent()->willReturn($event);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $isParticipantVisio = new IsParticipantVisio($extraDataRepository->reveal());

        $this->assertTrue($isParticipantVisio->isSatisfiedBy($participant->reveal()));
    }
}
