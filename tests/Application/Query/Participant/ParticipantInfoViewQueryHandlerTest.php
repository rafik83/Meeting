<?php

namespace Proximum\Vimeet\Application\Query\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ParticipantInfoViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $user   = new User('user@user.com', 'salt', 'password', $locale);

        $sheet       = SheetFactory::create(null, $user);
        $participant = new Participant($sheet, $user, [], true, new \DateTime());

        // Expected
        $participantInfoViewExpected = new ParticipantInfoView(
            'firstname',
            'lastname',
            ''
        );

        // Mock
        $participantRepository  = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);

        $participantRepository
            ->getParticipantForUserAndSheet($user, $sheet)
            ->shouldBeCalled()
            ->willReturn($participant);

        $participantInfoGuesser
            ->guessParticipantFirstName($participant, $locale)
            ->shouldBeCalled()
            ->willReturn('firstname');

        $participantInfoGuesser
            ->guessParticipantLastName($participant, $locale)
            ->shouldBeCalled()
            ->willReturn('lastname');

        $handler = new ParticipantMailViewQueryHandler(
            $participantRepository->reveal(),
            $participantInfoGuesser->reveal()
        );

        $participantInfoView = $handler->handle(new ParticipantMailViewQuery($sheet, $user));

        $this->assertEquals($participantInfoViewExpected, $participantInfoView);
    }
}
