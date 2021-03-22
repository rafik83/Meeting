<?php


namespace Proximum\Vimeet\Tests\Application\Query\Happening;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Happening\CanEvaluateHappening;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CanEvaluateHappeningTest extends TestCase
{
    private $happening, $user, $sheetRepository, $scanRepository, $event;

    public function setUp(): void {
        $this->happening = $this->prophesize(Happening::class);
        $this->user = $this->prophesize(User::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testNotMustEvaluateHappening()
    {
        $this->happening->mustEvaluateHappening()->shouldBeCalled()->willReturn(false);
        $canEvaluateHappening = new CanEvaluateHappening($this->sheetRepository->reveal(), $this->scanRepository->reveal());
        $result = $canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal());
        self::assertFalse($result);
    }

    public function testHasSpeaker()
    {
        $this->happening->mustEvaluateHappening()->shouldBeCalled()->willReturn(true);
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(true);
        $canEvaluateHappening = new CanEvaluateHappening($this->sheetRepository->reveal(), $this->scanRepository->reveal());
        $result = $canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal());
        self::assertFalse($result);
    }

    public function testHasNotUserBeenScannedForHappening()
    {
        $this->happening->mustEvaluateHappening()->shouldBeCalled()->willReturn(true);
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(false);
        $this->happening->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->happening->getId()->shouldBeCalled()->willReturn(1);
        $this->scanRepository->hasScanForUserEventTypeAndObjectId(
            $this->user->reveal(),
            $this->event->reveal(),
            'happening_entrance',
            1
        )->shouldBeCalled()->willReturn(false);

        $canEvaluateHappening = new CanEvaluateHappening($this->sheetRepository->reveal(), $this->scanRepository->reveal());
        $result = $canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal());
        self::assertFalse($result);
    }

    public function testSpeakerWithoutUser()
    {
        $this->happening->mustEvaluateHappening()->shouldBeCalled()->willReturn(true);
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(false);
        $this->happening->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->happening->getId()->shouldBeCalled()->willReturn(1);
        $this->scanRepository->hasScanForUserEventTypeAndObjectId(
            $this->user->reveal(),
            $this->event->reveal(),
            'happening_entrance',
            1
        )->shouldBeCalled()->willReturn(true);

        $speaker = $this->prophesize(Happening\Speaker::class);
        $speaker->getUser()->shouldBeCalled()->willReturn(null);
        $this->happening->getSpeakers()->shouldBeCalled()->willReturn([$speaker->reveal()]);

        $sheet = $this->prophesize(Sheet::class);
        $this->sheetRepository->getAllSheetsByUserAndEvent(
            $this->user->reveal(),
            $this->event->reveal()
        )->shouldBeCalled()->willReturn([$sheet->reveal()]);

        $canEvaluateHappening = new CanEvaluateHappening($this->sheetRepository->reveal(), $this->scanRepository->reveal());
        $result = $canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal());
        self::assertTrue($result);
    }

    public function testHaveCommonSheet()
    {
        $this->happening->mustEvaluateHappening()->shouldBeCalled()->willReturn(true);
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(false);
        $this->happening->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->happening->getId()->shouldBeCalled()->willReturn(1);
        $this->scanRepository->hasScanForUserEventTypeAndObjectId(
            $this->user->reveal(),
            $this->event->reveal(),
            'happening_entrance',
            1
        )->shouldBeCalled()->willReturn(true);

        $speaker = $this->prophesize(Happening\Speaker::class);
        $userSpeaker = $this->prophesize(User::class);
        $speaker->getUser()->shouldBeCalled()->willReturn($userSpeaker->reveal());
        $this->happening->getSpeakers()->shouldBeCalled()->willReturn([$speaker->reveal()]);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(3);

        $this->sheetRepository->getAllSheetsByUserAndEvent(
            $userSpeaker->reveal(),
            $this->event->reveal()
        )->shouldBeCalled()->willReturn([$sheet->reveal()]);

        $this->sheetRepository->getAllSheetsByUserAndEvent(
            $this->user->reveal(),
            $this->event->reveal()
        )->shouldBeCalled()->willReturn([$sheet->reveal()]);

        $canEvaluateHappening = new CanEvaluateHappening($this->sheetRepository->reveal(), $this->scanRepository->reveal());
        $result = $canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal());
        self::assertFalse($result);
    }

    public function testNoCommonSheet()
    {
        $this->happening->mustEvaluateHappening()->shouldBeCalled()->willReturn(true);
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(false);
        $this->happening->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->happening->getId()->shouldBeCalled()->willReturn(1);
        $this->scanRepository->hasScanForUserEventTypeAndObjectId(
            $this->user->reveal(),
            $this->event->reveal(),
            'happening_entrance',
            1
        )->shouldBeCalled()->willReturn(true);

        $speaker = $this->prophesize(Happening\Speaker::class);
        $userSpeaker = $this->prophesize(User::class);
        $speaker->getUser()->shouldBeCalled()->willReturn($userSpeaker->reveal());
        $this->happening->getSpeakers()->shouldBeCalled()->willReturn([$speaker->reveal()]);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(3);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(7);
        $this->sheetRepository->getAllSheetsByUserAndEvent(
            $userSpeaker->reveal(),
            $this->event->reveal()
        )->shouldBeCalled()->willReturn([$sheet->reveal()]);

        $this->sheetRepository->getAllSheetsByUserAndEvent(
            $this->user->reveal(),
            $this->event->reveal()
        )->shouldBeCalled()->willReturn([$sheet2->reveal()]);

        $canEvaluateHappening = new CanEvaluateHappening($this->sheetRepository->reveal(), $this->scanRepository->reveal());
        $result = $canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal());
        self::assertTrue($result);
    }
}
