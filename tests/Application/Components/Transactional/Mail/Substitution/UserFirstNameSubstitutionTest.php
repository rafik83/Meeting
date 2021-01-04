<?php


namespace Proximum\Vimeet\tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\UserFirstNameSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PreparePreRegisterMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class UserFirstNameSubstitutionTest extends TestCase
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
        $user->getFirstName()->shouldBeCalled()->willReturn('UserFirstName');
        $event = $this->prophesize(Event::class);

        $prepareMail = new PrepareUserRegisteredMailView($event->reveal(), $user->reveal(), $locale);

        $substitution = new UserFirstNameSubstitution($this->participantInfoGuesser->reveal());

        $result = $substitution->substitute($prepareMail);
        $expected = 'UserFirstName';

        $this->assertEquals($expected, $result);
    }

    public function testSubstituteWithUserAsParticipant()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $user->getFirstName()->shouldBeCalled()->willReturn('UserFirstName');
        $locale = 'fr';
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);

        $prepareMail = new PreparePreRegisterMailView($event->reveal(), $user->reveal(), $locale, $sheet->reveal(), $participant->reveal());

        $sheet->getUserParticipant($prepareMail->user)->shouldBeCalled()->willReturn(null);

        $substitution = new UserFirstNameSubstitution($this->participantInfoGuesser->reveal());

        $result = $substitution->substitute($prepareMail);
        $expected = 'UserFirstName';

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
            ->guessParticipantFirstName($prepareMail->participant, $locale)
            ->shouldBeCalled()
            ->willReturn('ParticipantFirstName')
        ;

        $substitution = new UserFirstNameSubstitution($this->participantInfoGuesser->reveal());

        $result = $substitution->substitute($prepareMail);
        $expected = 'ParticipantFirstName';

        $this->assertEquals($expected, $result);
    }
}
