<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Agenda\UpdateAgendaConfirmedStatus;
use Proximum\Vimeet\Application\Command\Sheet\Agenda\UpdateAgendaConfirmedStatusHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Agenda\ConfirmationCalculator;

class UpdateAgendaConfirmedStatusHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $event = $this->prophesize(Event::class);
        $user  = $this->prophesize(User::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet4 = $this->prophesize(Sheet::class);
        $sheet1->getAgendaConfirmedStatus()->willReturn(Sheet::AGENDA_NOT_CONCERNED);
        $sheet2->getAgendaConfirmedStatus()->willReturn(Sheet::AGENDA_NOT_CONCERNED);
        $sheet3->getAgendaConfirmedStatus()->willReturn(Sheet::AGENDA_NONE_CONFIRMED);
        $sheet4->getAgendaConfirmedStatus()->willReturn(Sheet::AGENDA_ALL_CONFIRMED);

        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $confirmationCalculator = $this->prophesize(ConfirmationCalculator::class);

        // Expected
        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal(), $sheet4->reveal()])
        ;

        $confirmationCalculator
            ->getConfirmationStatusForSheet($sheet1->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet::AGENDA_NOT_CONCERNED)
        ;

        $confirmationCalculator
            ->getConfirmationStatusForSheet($sheet2->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet::AGENDA_NONE_CONFIRMED)
        ;

        $confirmationCalculator
            ->getConfirmationStatusForSheet($sheet3->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet::AGENDA_PARTLY_CONFIRMED)
        ;

        $confirmationCalculator
            ->getConfirmationStatusForSheet($sheet4->reveal())
            ->shouldBeCalled()
            ->willReturn(Sheet::AGENDA_ALL_CONFIRMED)
        ;

        $sheet2->setAgendaConfirmedStatus(Sheet::AGENDA_NONE_CONFIRMED)->shouldBeCalled();
        $sheet3->setAgendaConfirmedStatus(Sheet::AGENDA_PARTLY_CONFIRMED)->shouldBeCalled();

        $sheetRepository->set($sheet2->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet3->reveal())->shouldBeCalled();

        // UpdateAgendaConfirmedStatusHandler
        $updateAgendaConfirmedStatusHandler = new UpdateAgendaConfirmedStatusHandler(
            $sheetRepository->reveal(),
            $confirmationCalculator->reveal()
        );
        $updateAgendaConfirmedStatusHandler->handle(new UpdateAgendaConfirmedStatus($event->reveal(), $user->reveal()));
    }
}
