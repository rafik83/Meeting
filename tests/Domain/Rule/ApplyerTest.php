<?php

namespace Proximum\Vimeet\Tests\Domain\Rule;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Rule\Composer;
use Proximum\Vimeet\Domain\Rule\Exception\NoRuleException;

class ApplyerTest extends TestCase
{
    public function testApplyRuleForParticipantCardWithoutRules()
    {
        $cardView = new CardView(1, false, 'firstname', 'lastname', 'position', 'avatar', false, 12);
        $expectedCardView = new CardView(1, false, '', '', '', '', false, 12);
        $expectedCardView->initials = 'FL';

        $composer = $this->prophesize(Composer::class);
        $composer->compose([])->shouldBeCalled()->willThrow(NoRuleException::class);
        $applyer = new Applyer($composer->reveal());

        $applyer->applyRuleForParticipantCard($cardView, []);

        $this->assertEquals($expectedCardView, $cardView);
    }

    public function testApplyRuleForParticipantCard()
    {
        $rule = $this->prophesize(Rule::class);
        $composedRule = new ComposedRule();
        $composedRule->tags = [
            Tag::PARTICIPANT_FIRSTNAME,
            Tag::PARTICIPANT_AVATAR,
        ];
        $cardView = new CardView(1, false, 'firstname', 'lastname', 'position', 'avatar', false, 12);
        $expectedCardView = new CardView(1, false, 'firstname', '', '', 'avatar', false, 12);
        $expectedCardView->initials = 'FL';

        $composer = $this->prophesize(Composer::class);
        $composer->compose([$rule->reveal()])->shouldBeCalled()->willReturn($composedRule);
        $applyer = new Applyer($composer->reveal());

        $applyer->applyRuleForParticipantCard($cardView, [$rule->reveal()]);

        $this->assertEquals($expectedCardView, $cardView);
    }

    public function testApplyRuleForCardListWithoutRules()
    {
        $cardView  = new CardView(1, false, 'firstname', 'lastname', 'position', 'avatar', false, 12);
        $cardView2 = new CardView(2, false, 'otherFirstname2', 'lastname2', 'position2', 'avatar2', false, 12);
        $expectedCardView = new CardView(1, false, '', '', '', '', false, 12);
        $expectedCardView->initials = 'FL';
        $expectedCardView2 = new CardView(2, false, '', '', '', '', false, 12);
        $expectedCardView2->initials = 'OL';

        $cardListView = new CardListView();
        $cardListView->cardViews = [
            $cardView,
            $cardView2,
        ];

        $expectedCardListView = new CardListView();
        $expectedCardListView->cardViews = [
            $expectedCardView,
            $expectedCardView2,
        ];

        $composer = $this->prophesize(Composer::class);
        $composer->compose([])->shouldBeCalled()->willThrow(NoRuleException::class);
        $applyer = new Applyer($composer->reveal());

        $applyer->applyRuleForCardList($cardListView, []);

        $this->assertEquals($expectedCardListView, $cardListView);
    }

    public function testApplyRuleForCardList()
    {
        $rule = $this->prophesize(Rule::class);
        $cardView  = new CardView(1, false, 'firstname', 'lastname', 'position', 'avatar', false, 12);
        $cardView2 = new CardView(2, false, 'otherFirstname2', 'lastname2', 'position2', 'avatar2', false, 12);
        $expectedCardView = new CardView(1, false, 'firstname', '', '', 'avatar', false, 12);
        $expectedCardView->initials = 'FL';
        $expectedCardView2 = new CardView(2, false, 'otherFirstname2', '', '', 'avatar2', false, 12);
        $expectedCardView2->initials = 'OL';

        $composedRule = new ComposedRule();
        $composedRule->tags = [
            Tag::PARTICIPANT_FIRSTNAME,
            Tag::PARTICIPANT_AVATAR,
        ];

        $cardListView = new CardListView();
        $cardListView->cardViews = [
            $cardView,
            $cardView2,
        ];

        $expectedCardListView = new CardListView();
        $expectedCardListView->cardViews = [
            $expectedCardView,
            $expectedCardView2,
        ];

        $composer = $this->prophesize(Composer::class);
        $composer->compose([$rule->reveal()])->shouldBeCalled()->willReturn($composedRule);
        $applyer = new Applyer($composer->reveal());

        $applyer->applyRuleForCardList($cardListView, [$rule->reveal()]);

        $this->assertEquals($expectedCardListView, $cardListView);
    }
}
