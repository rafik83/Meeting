<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Components\Meeting\AllowTransformRequestIntoMeetingOnDday;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class AllowTransformRequestIntoMeetingOnDdayTest extends TestCase
{
    public function testInvoke() {

        $rule = $this->prophesize(Rule::class);
        $rule->getRequestAutomaticallyTransformedIntoMeeting()->shouldBeCalled()->willReturn(true);

        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository->getBySeerSheetAndSeeableSheet(Argument::any(), Argument::any())->shouldBeCalled()->willReturn([$rule->reveal()]);

        $date = new \DateTime('2020-08-31');
        $ddayGuesser = new DDayGuesser($date,true);

        $request = $this->prophesize(Request::class);

        $event = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->isVisio()->shouldBeCalled()->willReturn(true);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $request->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $request->getFromSheet()->shouldBeCalled()->willReturn($this->prophesize(Sheet::class));
        $request->getToSheet()->shouldBeCalled()->willReturn($this->prophesize(Sheet::class));
        $event->hasDay()->shouldBeCalled()->willReturn(true);

        $firstDay = $this->prophesize(Event\Day::class);
        $firstDay->getStartTime()->shouldBeCalled()->willReturn(new \DateTime('2020-08-30T08:00'));

        $lastDay = $this->prophesize(Event\Day::class);
        $lastDay->getEndTime()->shouldBeCalled()->willReturn(new \DateTime('2020-09-02T18:00'));

        $event->getFirstDay()->shouldBeCalled()->willReturn($firstDay->reveal());
        $event->getLastDay()->shouldBeCalled()->willReturn($lastDay->reveal());

        $invoke = new AllowTransformRequestIntoMeetingOnDday(
            $ddayGuesser,
            $ruleRepository->reveal()
    );
        $result = $invoke->__invoke($request->reveal());
        $this->assertTrue($result);
    }
}
