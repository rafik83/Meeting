<?php

namespace Proximum\Vimeet\Tests\Domain\User\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\User\Sheet\HasAccessToSheet;
use PHPUnit\Framework\TestCase;

class HasAccessToSheetTest extends TestCase
{
    public function testHasAccess(): void
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);

        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()])
        ;

        $hasAccessToSheet = new HasAccessToSheet($sheetRepository->reveal());
        $this->assertTrue($hasAccessToSheet->isSatisfiedBy($user->reveal(), $event->reveal(), $sheet3->reveal()));
    }

    public function testHasNoAccess(): void
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);

        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;

        $hasAccessToSheet = new HasAccessToSheet($sheetRepository->reveal());
        $this->assertFalse($hasAccessToSheet->isSatisfiedBy($user->reveal(), $event->reveal(), $sheet3->reveal()));
    }
}
