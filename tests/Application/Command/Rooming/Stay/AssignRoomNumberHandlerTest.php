<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignRoomNumber;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignRoomNumberHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;

class AssignRoomNumberHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $accommodation = $this->prophesize(Accommodation::class);

        $arrival = new \DateTime();
        $departure = new \DateTime();

        $stay = new Stay(
            $event->reveal(),
            $user->reveal(),
            $arrival,
            $departure,
            $accommodation->reveal(),
            'single',
            'A123'
        );
        $comment = 'A124';

        $stayRepository = $this->prophesize(StayRepositoryInterface::class);
        $expected = new Stay(
            $event->reveal(),
            $user->reveal(),
            $arrival,
            $departure,
            $accommodation->reveal(),
            'single',
            'A124'
        );
        $stayRepository->update($expected)->shouldBeCalled();

        $command = new AssignRoomNumber($stay, $comment);
        $handler = new AssignRoomNumberHandler($stayRepository->reveal());

        $handler->handle($command);
    }
}
