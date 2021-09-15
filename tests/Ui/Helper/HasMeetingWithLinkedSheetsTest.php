<?php


namespace Proximum\Vimeet\Tests\Ui\Helper;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Ui\Helper\HasMeetingWithLinkedSheets;

class HasMeetingWithLinkedSheetsTest extends TestCase
{
    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $meetingRequest;

    /** @var ObjectProphecy */
    private $toSheet;

    /** @var ObjectProphecy */
    private $fromSheet;

    /** @var ObjectProphecy */
    private $toSheetLinked;

    /** @var ObjectProphecy */
    private $linkedSheet;

    public function setUp()
    {
        $this->toSheet = $this->prophesize(Sheet::class);
        $this->toSheetLinked = $this->prophesize(Sheet::class);
        $this->fromSheet = $this->prophesize(Sheet::class);
        $this->linkedSheet = $this->prophesize(Sheet\LinkedSheets::class);

        $this->meetingRequest = $this->prophesize(Request::class);
        $this->meetingRequest->getToSheet()->willReturn($this->toSheet->reveal());
        $this->meetingRequest->getFromSheet()->willReturn($this->fromSheet->reveal());
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
    }

    public function test_has_meeting_with_linked_sheet()
    {
        $this->linkedSheet->getSheets()->willReturn([$this->toSheet, $this->toSheetLinked]);
        $this->toSheet->hasLinkedSheets()->willReturn(true);
        $this->toSheet->getLinkedSheets()->willReturn($this->linkedSheet);
        $this->fromSheet->hasLinkedSheets()->willReturn(false);
        $this->fromSheet->getLinkedSheets()->willReturn(null);

        $this->meetingRepository
            ->hasAtLeastOneMeeting([$this->toSheet->reveal(), $this->toSheetLinked->reveal()], [$this->fromSheet->reveal()])
            ->shouldBeCalled()
            ->willReturn(true);

        $hasMeetingWithLinkedSheets = new HasMeetingWithLinkedSheets($this->meetingRepository->reveal());
        $this->assertTrue($hasMeetingWithLinkedSheets->isSatisfiedBy($this->meetingRequest->reveal()));
    }

    public function test_has_no_meeting_with_linked_sheet(){

        $this->toSheet->hasLinkedSheets()->willReturn(false);
        $this->toSheet->getLinkedSheets()->willReturn(null);
        $this->fromSheet->hasLinkedSheets()->willReturn(false);
        $this->fromSheet->getLinkedSheets()->willReturn(null);

        $this->meetingRepository
            ->hasAtLeastOneMeeting([$this->toSheet->reveal()], [$this->fromSheet->reveal()])
            ->shouldNotBeCalled();

        $hasMeetingWithLinkedSheets = new HasMeetingWithLinkedSheets($this->meetingRepository->reveal());
        $this->assertFalse($hasMeetingWithLinkedSheets->isSatisfiedBy($this->meetingRequest->reveal()));
    }
}
