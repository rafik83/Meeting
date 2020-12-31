<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\LastLogin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLogin;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLoginHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateLastLoginHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet->reveal()])
        ;

        $sheet->setLastLoginAt($dateTime)->shouldBeCalled()->willReturn($sheet->reveal());
        $sheetRepository->set($sheet->reveal())->shouldBeCalled();

        $updateLastLoginHandler = new UpdateLastLoginHandler($sheetRepository->reveal(), $dateTime);
        $updateLastLoginHandler->handle(new UpdateLastLogin($event->reveal(), $user->reveal()));
    }
}
