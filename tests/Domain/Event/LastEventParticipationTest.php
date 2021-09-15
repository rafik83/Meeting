<?php

namespace Domain\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\LastEventParticipation;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class LastEventParticipationTest extends TestCase
{
    public function testGetLastEvent()
    {
        $user = UserFactory::create();
        $currentEvent = EventFactory::createEvent();
        $sheet = SheetFactory::create($currentEvent);
        $lastParticipation = ParticipantFactory::create($sheet, $user);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $participantRepository->getLastEventParticipation($user, $currentEvent)
            ->shouldBeCalled()
            ->willReturn($lastParticipation);

        $lastEventParticipation = new LastEventParticipation(
            $participantRepository->reveal()
        );

        $lastParticipationResult = $lastEventParticipation->getLastEventParticipation($user, $currentEvent);

        $this->assertEquals($lastParticipation, $lastParticipationResult);
    }

    public function testGetLastEventFromDuplicateEvent()
    {
        $user = UserFactory::create();

        $duplicateFrom = EventFactory::createEvent();
        $currentEvent = EventFactory::createEvent(null, null, ['fr'], null, $duplicateFrom);

        $sheet = SheetFactory::create($currentEvent);
        $lastParticipation = ParticipantFactory::create($sheet, $user);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $participantRepository->getParticipantsByUserForEvent($user->getId(), $duplicateFrom)
            ->shouldBeCalled()->willReturn([$lastParticipation]);

        $participantRepository->getLastEventParticipation($user, $currentEvent)
            ->shouldNotBeCalled();

        $lastEventParticipation = new LastEventParticipation(
            $participantRepository->reveal()
        );

        $lastParticipationResult = $lastEventParticipation->getLastEventParticipation($user, $currentEvent);

        $this->assertEquals($lastParticipation, $lastParticipationResult);
    }
}
