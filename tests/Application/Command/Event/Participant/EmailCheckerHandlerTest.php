<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\Participant\EmailChecker;
use Proximum\Vimeet\Application\Command\Event\Participant\EmailCheckerHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class EmailCheckerHandlerTest extends TestCase
{
    public function testUnknownUser(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);

        // prophecy dependencies
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $userRepository->findByEmail('maria.salomea.skłodowska@science.fr')
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        // run test
        $query = new EmailChecker($event->reveal());
        $query->email = 'maria.salomea.skłodowska@science.fr';
        $handler = new EmailCheckerHandler($userRepository->reveal(), $participantRepository->reveal());

        $result = $handler->handle($query);

        $this->assertEquals(EmailCheckerHandler::EMAIL_UNKNOWN, $result);
    }

    public function testUserKnownFromVimeet(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        // prophecy dependencies
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $userRepository->findByEmail('maria.salomea.skłodowska@science.fr')
            ->shouldBeCalled()
            ->willReturn($user->reveal())
        ;

        $participantRepository->getAllParticipantForUser($event->reveal(), $user->reveal())
            ->willReturn([])
        ;

        // run test
        $query = new EmailChecker($event->reveal());
        $query->email = 'maria.salomea.skłodowska@science.fr';
        $handler = new EmailCheckerHandler($userRepository->reveal(), $participantRepository->reveal());

        $result = $handler->handle($query);

        $this->assertEquals(EmailCheckerHandler::EMAIL_KNOWN_FROM_VIMEET, $result);
    }

    public function testUserKnownFromEvent(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);

        // prophecy dependencies
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $userRepository->findByEmail('maria.salomea.skłodowska@science.fr')
            ->shouldBeCalled()
            ->willReturn($user->reveal())
        ;

        $participantRepository->getAllParticipantForUser($event->reveal(), $user->reveal())
            ->willReturn([$participant])
        ;

        // run test
        $query = new EmailChecker($event->reveal());
        $query->email = 'maria.salomea.skłodowska@science.fr';
        $handler = new EmailCheckerHandler($userRepository->reveal(), $participantRepository->reveal());

        $result = $handler->handle($query);

        $this->assertEquals(EmailCheckerHandler::EMAIL_KNOWN_FROM_EVENT, $result);
    }
}
