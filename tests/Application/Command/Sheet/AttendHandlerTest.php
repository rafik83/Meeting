<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Attend;
use Proximum\Vimeet\Application\Command\Sheet\AttendHandler;
use Proximum\Vimeet\Application\Components\Sheet\HappeningParticipation\EnableDisableManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AttendHandlerTest extends TestCase
{
    public function testHandleCancelAttendance()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->attend()->willReturn(true);
        $sheet->setAttendance(false)->shouldBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->removeMeetingOfSheet($sheet->reveal())->shouldBeCalled();

        $sheetRepository   = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($sheet->reveal())->shouldBeCalled();

        $happeningsEnableDisableManager = $this->prophesize(EnableDisableManager::class);
        $happeningsEnableDisableManager->update($sheet->reveal(), EnableDisableManager::DISABLE_HAPPENING_PARTICIPATION)->shouldBeCalled();

        $handler = new AttendHandler(
            $meetingRepository->reveal(),
            $sheetRepository->reveal(),
            $happeningsEnableDisableManager->reveal()
        );

        $attend = new Attend($sheet->reveal());
        $attend->attend = false;
        $handler->handle($attend);
    }

    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->attend()->willReturn(false);
        $sheet->setAttendance(true)->shouldBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->removeMeetingOfSheet($sheet->reveal())->shouldNotBeCalled();

        $sheetRepository   = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($sheet->reveal())->shouldBeCalled();

        $happeningsEnableDisableManager = $this->prophesize(EnableDisableManager::class);
        $happeningsEnableDisableManager->update($sheet->reveal(), EnableDisableManager::ENABLE_HAPPENING_PARTICIPATION)->shouldBeCalled();

        $handler = new AttendHandler(
            $meetingRepository->reveal(),
            $sheetRepository->reveal(),
            $happeningsEnableDisableManager->reveal()
        );

        $attend = new Attend($sheet->reveal());
        $attend->attend = true;
        $handler->handle($attend);
    }
}
