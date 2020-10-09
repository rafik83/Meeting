<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Participant\UpdateNetworkingChatViewedAt;
use Proximum\Vimeet\Application\Command\Participant\UpdateNetworkingChatViewedAtHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateNetworkingChatViewedAtHandlerTest extends TestCase
{
    public function test(): void
    {
        $user  = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);

        $sheet->getEvent()->shouldBeCalled()->willReturn($event);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->updateAllNetworkingChatViewedAt($user->reveal(), $event->reveal());

        $updateTime = new DateTime();

        $command = new UpdateNetworkingChatViewedAt($sheet->reveal(), $user->reveal(), $updateTime);
        $handler = new UpdateNetworkingChatViewedAtHandler($participantRepository->reveal());

        $handler->handle($command);
    }
}
