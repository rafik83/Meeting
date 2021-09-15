<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\GuestEmailSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareParticipantAddedMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class GuestEmailSubstitutionTest extends TestCase
{
    public function testWithoutGuest()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $mail = new PrepareUserRegisteredMailView($event->reveal(), $user->reveal(), 'fr');

        $substitution = new GuestEmailSubstitution();
        $result = $substitution->substitute($mail);

        $this->assertEquals('', $result);
    }

    public function testSubstitute()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getEmail()->shouldBeCalled()->willReturn('user@example.net');

        $mail = new PrepareParticipantAddedMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $participant->reveal()
        );

        $substitution = new GuestEmailSubstitution();
        $result = $substitution->substitute($mail);

        $this->assertEquals('user@example.net', $result);
    }
}
