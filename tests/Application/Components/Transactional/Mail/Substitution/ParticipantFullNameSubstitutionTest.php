<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\ParticipantFullNameSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareParticipantAddedMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCompleteProfileMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantFullNameSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);

        $mail = new PrepareUserCompleteProfileMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $participant->reveal()
        );

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Jean Paul')
        ;
        $substitution = new ParticipantFullNameSubstitution($participantInfoGuesser->reveal());
        $result = $substitution->substitute($mail);

        $this->assertEquals('Jean Paul', $result);
    }

    public function testSubstituteWithoutParticipant()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $user->getFullname()->willReturn('Jean Paul');

        $mail = new PrepareUserRegisteredMailView(
            $event->reveal(),
            $user->reveal(),
            'fr'
        );

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName(Argument::any())->shouldNotBeCalled();

        $substitution = new ParticipantFullNameSubstitution($participantInfoGuesser->reveal());
        $result = $substitution->substitute($mail);

        $this->assertEquals('Jean Paul', $result);
    }

    public function testSubstituteWithoutParticipantButWithSheet()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $guest = $this->prophesize(Participant::class);
        $participant = $this->prophesize(Participant::class);

        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant->reveal());

        $mail = new PrepareParticipantAddedMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $guest->reveal()
        );

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Jean Paul');


        $substitution = new ParticipantFullNameSubstitution($participantInfoGuesser->reveal());
        $result = $substitution->substitute($mail);

        $this->assertEquals('Jean Paul', $result);
    }

    public function testSubstituteWithoutParticipantButWithSheetButOwnerNotParticipant()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $user->getFullname()->willReturn('Jean Paul');
        $sheet = $this->prophesize(Sheet::class);
        $guest = $this->prophesize(Participant::class);

        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn(null);

        $mail = new PrepareParticipantAddedMailView(
            $event->reveal(),
            $user->reveal(),
            'fr',
            $sheet->reveal(),
            $guest->reveal()
        );

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessParticipantCompleteName(Argument::any())
            ->shouldNotBeCalled()
        ;

        $substitution = new ParticipantFullNameSubstitution($participantInfoGuesser->reveal());
        $result = $substitution->substitute($mail);

        $this->assertEquals('Jean Paul', $result);
    }
}
