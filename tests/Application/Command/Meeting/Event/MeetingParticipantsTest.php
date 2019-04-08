<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Event;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class MeetingParticipantsTest extends TestCase
{
    /**
     * @var ObjectProphecy|Request
     */
    private $request;

    /**
     * @var ObjectProphecy|Sheet
     */
    private $sheet;

    /**
     * @var ObjectProphecy|Type
     */
    private $type;

    /**
     * @var ObjectProphecy|Participant
     */
    private $fromParticipant;

    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->type = $this->prophesize(Type::class);

        $this->fromParticipant = $this->prophesize(Participant::class);

        $this->sheet->getType()->willReturn($this->type->reveal());

        $this->request->isSender($this->sheet->reveal())
            ->willReturn(true);

        $this->request->isReceiver($this->sheet->reveal())
            ->willReturn(false);
    }

    public function testInvalidSheet(): void
    {
        // prepare data
        $request = $this->prophesize(Request::class);
        $invalidSheet = $this->prophesize(Sheet::class);

        $request->isSender($invalidSheet)
            ->shouldBeCalled()
            ->willReturn(false);

        $request->isReceiver($invalidSheet)
            ->shouldBeCalled()
            ->willReturn(false);

        // run tests
        $this->expectException(InvalidArgumentException::class);

        $meetingParticipants = new MeetingParticipants();
        $meetingParticipants->getMeetingParticipants($request->reveal(), $invalidSheet->reveal());
    }

    public function testSimple(): void
    {
        $this->sheet->hasLinkedSheets()->willReturn(false);

        $this->type->areAllSheetParticipantsAssignedToMeeting()->willReturn(false);

        $this->request->getParticipants($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$this->fromParticipant->reveal()]);

        // run tests
        $meetingParticipants = new MeetingParticipants();
        $result = $meetingParticipants->getMeetingParticipants($this->request->reveal(), $this->sheet->reveal());

        $expected = [$this->fromParticipant->reveal()];

        $this->assertEquals($expected, $result);
    }

    public function testNotEverybodyAssignedButLinkedSheets(): void
    {
        $this->sheet->hasLinkedSheets()->willReturn(true);

        $this->type->areAllSheetParticipantsAssignedToMeeting()->willReturn(false);

        $this->request->getParticipants($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$this->fromParticipant->reveal()]);

        // run tests
        $meetingParticipants = new MeetingParticipants();
        $result = $meetingParticipants->getMeetingParticipants($this->request->reveal(), $this->sheet->reveal());

        $expected = [$this->fromParticipant->reveal()];

        $this->assertEquals($expected, $result);
    }

    public function testEverybodyAssignedInNotLinkedSheets(): void
    {
        $this->sheet->hasLinkedSheets()->willReturn(false);

        $this->type->areAllSheetParticipantsAssignedToMeeting()->willReturn(true);

        $this->sheet->getParticipantsArray()->shouldBeCalled()->willReturn([$this->fromParticipant->reveal()]);

        // run tests
        $meetingParticipants = new MeetingParticipants();
        $result = $meetingParticipants->getMeetingParticipants($this->request->reveal(), $this->sheet->reveal());

        $expected = [$this->fromParticipant->reveal()];

        $this->assertEquals($expected, $result);
    }

    public function testEverybodyAssignedInLinkedSheets(): void
    {
        $this->sheet->hasLinkedSheets()->willReturn(true);

        $this->type->areAllSheetParticipantsAssignedToMeeting()->willReturn(true);

        $this->sheet->getLinkedSheetsParticipants()->shouldBeCalled()->willReturn([$this->fromParticipant->reveal()]);

        // run tests
        $meetingParticipants = new MeetingParticipants();
        $result = $meetingParticipants->getMeetingParticipants($this->request->reveal(), $this->sheet->reveal());

        $expected = [$this->fromParticipant->reveal()];

        $this->assertEquals($expected, $result);
    }
}
