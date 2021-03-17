<?php


namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Evaluation;


use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Evaluation\PreviousEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Evaluation\PreviousEvaluationCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\PreviousMeetingEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\PreviousMeetingEvaluationCheckerHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PreviousEvaluationCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event, $sheet, $user, $timeRange, $previousHappeningEvaluationCheckerHandler, $previousMeetingEvaluationCheckerHandler;

    public function setUp(): void
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->timeRange = $this->prophesize(TimeRangeInterface::class);
        $this->previousHappeningEvaluationCheckerHandler = $this->prophesize(PreviousHappeningEvaluationCheckerHandler::class);
        $this->previousMeetingEvaluationCheckerHandler = $this->prophesize(PreviousMeetingEvaluationCheckerHandler::class);
    }

    public function testMustVoteHappening()
    {
        $previousHappeningEvaluationChecker = new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            '/path/happening'
        );

        $redirectResponse = new RedirectResponse('http://example.org');
        $this->previousHappeningEvaluationCheckerHandler->__invoke($previousHappeningEvaluationChecker)->shouldBeCalled()->willReturn($redirectResponse);

        $handler = new PreviousEvaluationCheckerHandler(
            $this->previousHappeningEvaluationCheckerHandler->reveal(),
            $this->previousMeetingEvaluationCheckerHandler->reveal());

        $previousEvaluationChecker = new PreviousEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            '/path/happening'
        );

        $result = $handler($previousEvaluationChecker);
        self::assertEquals($redirectResponse, $result);
    }

    public function testMustVoteMeeting()
    {
        $previousHappeningEvaluationChecker = new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            '/path/meeting'
        );

        $previousMeetingEvaluationChecker = new PreviousMeetingEvaluationChecker(
            '/path/meeting',
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
        );

        $redirectResponse = new RedirectResponse('http://example.org');
        $this->previousHappeningEvaluationCheckerHandler->__invoke($previousHappeningEvaluationChecker)->shouldBeCalled()->willReturn(null);
        $this->previousMeetingEvaluationCheckerHandler->__invoke($previousMeetingEvaluationChecker)->shouldBeCalled()->willReturn($redirectResponse);

        $handler = new PreviousEvaluationCheckerHandler(
            $this->previousHappeningEvaluationCheckerHandler->reveal(),
            $this->previousMeetingEvaluationCheckerHandler->reveal());

        $previousEvaluationChecker = new PreviousEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            '/path/meeting'
        );

        $result = $handler($previousEvaluationChecker);
        self::assertEquals($redirectResponse, $result);
    }

    public function testNoVoteNeeded()
    {
        $previousHappeningEvaluationChecker = new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            '/path/meeting'
        );

        $previousMeetingEvaluationChecker = new PreviousMeetingEvaluationChecker(
            '/path/meeting',
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
        );

        $this->previousHappeningEvaluationCheckerHandler->__invoke($previousHappeningEvaluationChecker)->shouldBeCalled()->willReturn(null);
        $this->previousMeetingEvaluationCheckerHandler->__invoke($previousMeetingEvaluationChecker)->shouldBeCalled()->willReturn(null);

        $handler = new PreviousEvaluationCheckerHandler(
            $this->previousHappeningEvaluationCheckerHandler->reveal(),
            $this->previousMeetingEvaluationCheckerHandler->reveal());

        $previousEvaluationChecker = new PreviousEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            '/path/meeting'
        );

        $result = $handler($previousEvaluationChecker);
        self::assertNull($result);
    }
}
