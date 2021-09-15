<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SpotReferenceSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCompleteProfileMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;

class SpotReferenceSubstitutionTest extends TestCase
{
    public function testSubstituteWithoutSheet()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $mail = new PrepareUserRegisteredMailView(
            $event->reveal(),
            $user->reveal(),
            'fr'
        );

        $substitute = new SpotReferenceSubstitution();
        $result = $substitute->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstituteWithoutSpot()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $sheet->getSpot()->shouldBeCalled()->willReturn(null);

        $mail = new PrepareUserCompleteProfileMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $participant->reveal()
        );

        $substitute = new SpotReferenceSubstitution();
        $result = $substitute->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstituteWitSpot()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $spot = $this->prophesize(Spot::class);
        $spot->getReference()->shouldBeCalled()->willReturn('Reference A10');
        $sheet->getSpot()->shouldBeCalled()->willReturn($spot->reveal());

        $mail = new PrepareUserCompleteProfileMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $participant->reveal()
        );

        $substitute = new SpotReferenceSubstitution();
        $result = $substitute->substitute($mail);

        $this->assertEquals('Reference A10', $result);
    }
}
