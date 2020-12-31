<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SheetPlanningSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCompleteProfileMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Application\Query\Sheet\Planning\SheetPlanningViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Planning\SheetPlanningViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Planning\SheetPlanningView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetPlanningSubstitutionTest extends TestCase
{
    public function testSubstituteWithoutSheet()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $sheetPlanningViewQueryHandler = $this->prophesize(SheetPlanningViewQueryHandler::class);

        $mail = new PrepareUserRegisteredMailView(
            $event->reveal(),
            $user->reveal(),
            'fr'
        );

        $substitute = new SheetPlanningSubstitution($sheetPlanningViewQueryHandler->reveal());
        $result = $substitute->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstitute()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $sheetPlanningViewQueryHandler = $this->prophesize(SheetPlanningViewQueryHandler::class);
        $sheetPlanningViewQueryHandler
            ->handle(new SheetPlanningViewQuery($sheet->reveal() ,'fr', $participant->reveal()))
            ->shouldBeCalled()
            ->willReturn(new SheetPlanningView('Planning'))
        ;

        $mail = new PrepareUserCompleteProfileMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $participant->reveal()
        );

        $substitute = new SheetPlanningSubstitution($sheetPlanningViewQueryHandler->reveal());
        $result = $substitute->substitute($mail);

        $this->assertEquals('Planning', $result);
    }
}
