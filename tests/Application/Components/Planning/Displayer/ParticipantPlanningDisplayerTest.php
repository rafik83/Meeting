<?php

namespace Proximum\Vimeet\Tests\Application\Components\Planning\Displayer;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Application\Components\Planning\Displayer\ParticipantPlanningDisplayer;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantPlanningDisplayerTest extends TestCase
{
    public function testDisplay()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $locale = 'fr';
        $planningMarkdown = "Planning:\n**Jeudi Y Janvier**\n\n- 10:00 13:00 - TABLE A01 - Truc Muche\n";
        $planningHtml = '<div>Planning:<br><b>Jeudi Y Janvier</b><br><br>- 10:00 13:00 - TABLE A01 - Truc Muche</div>';

        // Mock
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $participantPlanningFormatter
            ->formatPlanningFromUserAndEventWithUnallocated($user->reveal(), $event->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn($planningMarkdown)
        ;
        $markdown = $this->prophesize(MarkdownAdapterInterface::class);
        $markdown
            ->toHtml($planningMarkdown)
            ->shouldBeCalled()
            ->willReturn($planningHtml)
        ;

        // Displayer
        $participantPlanningDisplayer = new ParticipantPlanningDisplayer(
            $participantPlanningFormatter->reveal(),
            $markdown->reveal()
        );
        $result = $participantPlanningDisplayer->display($event->reveal(), $user->reveal(), $locale);

        $expected = $planningHtml;

        $this->assertEquals($expected, $result);
    }

    public function testPreload()
    {
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);
        $users = [
            $user1->reveal(),
            $user2->reveal(),
            $user3->reveal(),
        ];
        $event = $this->prophesize(Event::class);

        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $participantPlanningFormatter
            ->preloadPlanningHandlerForUsersAndEvent($users, $event->reveal())
            ->shouldBeCalled();
        $markdown = $this->prophesize(MarkdownAdapterInterface::class);

        // Displayer
        $participantPlanningDisplayer = new ParticipantPlanningDisplayer(
            $participantPlanningFormatter->reveal(),
            $markdown->reveal()
        );
        $participantPlanningDisplayer->preloadForUsersAndEvent($users, $event->reveal());
    }
}
