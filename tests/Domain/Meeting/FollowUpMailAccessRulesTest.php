<?php

namespace Proximum\Vimeet\Tests\Domain\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRule;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRulesResolver;
use Proximum\Vimeet\Domain\Meeting\FollowUpMailAccessRules;
use Proximum\Vimeet\Tests\Factory\MeetingFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class FollowUpMailAccessRulesTest extends TestCase
{
    private ObjectProphecy $participantInfoAccessRulesResolver;
    private FollowUpMailAccessRules $followUpMailAccessRules;

    protected function setUp()
    {
        $this->participantInfoAccessRulesResolver = $this->prophesize(ParticipantInfoAccessRulesResolver::class);

        $this->followUpMailAccessRules = new FollowUpMailAccessRules($this->participantInfoAccessRulesResolver->reveal());
    }

    public function testCanSendEmail()
    {
        $meeting = MeetingFactory::createMeeting();
        $evaluatedSheet = SheetFactory::create();
        $participantInfoAccesRule = $this->prophesize(ParticipantInfoAccessRule::class);
        $participantInfoAccesRule->canSendFollowUpEmail(4)->willReturn(true);

        $result = $this->followUpMailAccessRules->canSendEmail(
            $meeting,
            $evaluatedSheet,
            $participantInfoAccesRule->reveal(),
            4
        );
        $this->assertTrue($result);
    }

    public function testCantSendEmailIfAlreadySent()
    {
        $evaluatedSheet = SheetFactory::create();
        $meeting = MeetingFactory::createMeeting($evaluatedSheet);
        $meeting->setFollowupSent($evaluatedSheet);
        $participantInfoAccesRule = $this->prophesize(ParticipantInfoAccessRule::class);
        $participantInfoAccesRule->canSendFollowUpEmail()->shouldNotBeCalled();

        $result = $this->followUpMailAccessRules->canSendEmail(
            $meeting,
            $evaluatedSheet,
            $participantInfoAccesRule->reveal(),
            4
        );
        $this->assertFalse($result);
    }

    public function testCantSendEmailIfRuleIsNotSatisfied()
    {
        $evaluatedSheet = SheetFactory::create();
        $meeting = MeetingFactory::createMeeting($evaluatedSheet);
        $participantInfoAccesRule = $this->prophesize(ParticipantInfoAccessRule::class);
        $participantInfoAccesRule->canSendFollowUpEmail(4)->willReturn(false);

        $result = $this->followUpMailAccessRules->canSendEmail(
            $meeting,
            $evaluatedSheet,
            $participantInfoAccesRule->reveal(),
            4
        );
        $this->assertFalse($result);
    }
}
