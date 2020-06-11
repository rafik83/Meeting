<?php

namespace Proximum\Vimeet\Tests\Application\Components\Rule;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRulesResolver;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class ParticipantInfoAccessRulesResolverTest extends TestCase
{

    private $ruleRepository;

    protected function setUp()
    {
        $this->ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
    }

    public function testEmptyRuleSet()
    {
        $participantInfoAccessRulesResolver = new ParticipantInfoAccessRulesResolver($this->ruleRepository->reveal());

        $event = $this->prophesize(Event::class);

        $seerSheet = $this->prophesize(Sheet::class);
        $seerSheet->getType()->shouldBecalled()->willreturn(new Type($event->reveal()));

        $seeableSheet = $this->prophesize(Sheet::class);
        $seeableSheet->getType()->shouldBecalled()->willreturn(new Type($event->reveal()));
        $seeableSheet->getEvent()->shouldBecalled()->willreturn($event->reveal());

        $this->ruleRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn([]);

        $noRule = $participantInfoAccessRulesResolver->getParticipantInfoAccessRule($seerSheet->reveal(), $seeableSheet->reveal());

        $this->assertTrue($noRule->isPhoneVisible(null));
        $this->assertTrue($noRule->isEmailVisible(null));
        $this->assertTrue($noRule->isPhoneVisible(1));
        $this->assertTrue($noRule->isEmailVisible(1));
    }

    public function testSingleRule()
    {
        $participantInfoAccessRulesResolver = new ParticipantInfoAccessRulesResolver($this->ruleRepository->reveal());

        $event = $this->prophesize(Event::class);

        $seerSheet = $this->prophesize(Sheet::class);
        $seerType = $this->prophesize(Type::class);
        $seerType->getId()->shouldBeCalled()->willReturn(1);
        $seerType->getCategories()->shouldBeCalled()->willReturn(new ArrayCollection());
        $seerSheet->getType()->shouldBecalled()->willreturn($seerType->reveal());

        $seeableSheet = $this->prophesize(Sheet::class);
        $seeableType = $this->prophesize(Type::class);
        $seeableType->getId()->shouldBeCalled()->willreturn(2);
        $seeableType->getCategories()->shouldBeCalled()->willReturn(new ArrayCollection());
        $seeableSheet->getType()->shouldBecalled()->willreturn($seeableType->reveal());
        $seeableSheet->getEvent()->shouldBecalled()->willreturn($event->reveal());

        $rule = $this->prophesize(Rule::class);
        // min grade for phone access is 4
        $rule->getPhoneAccessMinEvaluation()->shouldBeCalled()->willReturn(3);
        // min grade for email access is 3
        $rule->getEmailAccessMinEvaluation()->shouldBeCalled()->willReturn(2);
        $rule->getSeer()->shouldBeCalled()->willReturn($seerType->reveal());
        $rule->getSeeable()->shouldBeCalled()->willReturn($seeableType);

        $this->ruleRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn([$rule->reveal()]);

        $singleAccessRule = $participantInfoAccessRulesResolver->getParticipantInfoAccessRule($seerSheet->reveal(), $seeableSheet->reveal());

        $this->assertFalse($singleAccessRule->isPhoneVisible(null));
        $this->assertFalse($singleAccessRule->isEmailVisible(null));
        $this->assertFalse($singleAccessRule->isPhoneVisible(3));
        $this->assertFalse($singleAccessRule->isEmailVisible(2));
        $this->assertTrue($singleAccessRule->isPhoneVisible(4));
        $this->assertTrue($singleAccessRule->isEmailVisible(3));
    }
}
