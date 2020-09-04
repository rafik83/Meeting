<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Components\Meeting\AllowTransformRequestIntoMeeting;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class AllowTransformRequestIntoMeetingTest extends TestCase
{
    public function testInvoke() {

        $rule = $this->prophesize(Rule::class);
        $rule->getRequestAutomaticallyTransformedIntoMeeting()->shouldBeCalled()->willReturn(true);

        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository->getBySeerSheetAndSeeableSheet(Argument::any(), Argument::any())->shouldBeCalled()->willReturn([$rule->reveal()]);

        $date = new \DateTime('2020-08-31');

        $request = $this->prophesize(Request::class);

        $event = $this->prophesize(Event::class);
        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->isVisio()->shouldBeCalled()->willReturn(true);
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration->reveal());
        $request->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $request->getFromSheet()->shouldBeCalled()->willReturn($this->prophesize(Sheet::class));
        $request->getToSheet()->shouldBeCalled()->willReturn($this->prophesize(Sheet::class));

        $invoke = new AllowTransformRequestIntoMeeting($ruleRepository->reveal());
        $result = $invoke->__invoke($request->reveal());
        $this->assertTrue($result);

        $allowTransformRequestIntoMeeting = $this->prophesize(AllowTransformRequestIntoMeeting::class);
        $allowTransformRequestIntoMeeting->__invoke($request)->willReturn(true);
    }
}
