<?php


namespace Proximum\Vimeet\tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\UserLastNameSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PreparePreRegisterMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;


class UserLastNameSubstitutionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $participantInfoGuesser;

    public function setUp()
    {
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
    }

    public function testSubstituteWithNoSheet()
    {
        $locale = 'fr';
        $user = $this->prophesize(User::class);
        $user->getLastName()->shouldBeCalled()->willReturn('UserLastName');
        $event = $this->prophesize(Event::class);

        $prepareMail = new PrepareUserRegisteredMailView($event->reveal(), $user->reveal(), $locale);

        $substitution = new UserLastNameSubstitution($this->participantInfoGuesser->reveal());

        $result = $substitution->substitute($prepareMail);
        $expected = 'UserLastName';

        $this->assertEquals($expected, $result);
    }

    public function testSubstituteWithUserAsParticipant()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $user->getLastName()->shouldBeCalled()->willReturn('UserLastName');
        $locale = 'fr';
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);

        $prepareMail = new PreparePreRegisterMailView($event->reveal(), $user->reveal(), $locale, $sheet->reveal(), $participant->reveal());

        $sheet->getUserParticipant($prepareMail->user)->shouldBeCalled()->willReturn(null);

        $substitution = new UserLastNameSubstitution($this->participantInfoGuesser->reveal());

        $result = $substitution->substitute($prepareMail);
        $expected = 'UserLastName';

        $this->assertEquals($expected, $result);
    }

    public function testSubstituteWithSimpleUser()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $locale = 'fr';
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);

        $prepareMail = new PreparePreRegisterMailView($event->reveal(), $user->reveal(), $locale, $sheet->reveal(), $participant->reveal());

        $sheet->getUserParticipant($prepareMail->user)->shouldBeCalled()->willReturn($prepareMail->participant);

        $this->participantInfoGuesser
            ->guessParticipantLastName($prepareMail->participant, $locale)
            ->shouldBeCalled()
            ->willReturn('ParticipantLastName')
        ;

        $substitution = new UserLastNameSubstitution($this->participantInfoGuesser->reveal());

        $result = $substitution->substitute($prepareMail);
        $expected = 'ParticipantLastName';

        $this->assertEquals($expected, $result);
    }
}
