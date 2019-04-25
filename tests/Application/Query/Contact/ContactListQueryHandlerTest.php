<?php

namespace Proximum\Vimeet\Tests\Application\Query\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Contact\ContactPreviewView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ContactListQueryHandlerTest extends TestCase
{
    public function test()
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $user->getFullname()->willReturn('Carrie Fisher');
        $participant = $this->prophesize(Participant::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getUserParticipant($user->reveal())
            ->willReturn($participant->reveal())
        ;
        $sheet1->getTitle()->willReturn('New Republic');

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getUserParticipant($user->reveal())
            ->willReturn($participant->reveal())
        ;
        $sheet2->getTitle()->willReturn('Rebels');

        // prophecies dependencies
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);

        $userRepository->getMet($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([$user->reveal()])
        ;

        $sheetRepository->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet2->reveal(), $sheet1->reveal()])
        ;

        $participantInfoGuesser->guessParticipantInfos($participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    'participant_firstname' => 'Carrie',
                    'participant_lastname'  => 'Fisher',
                    'participant_avatar'    => 'http://far.away/leia.png',
                ]
            )
        ;

        // run tests
        $query = new GetContactListViewQuery($event->reveal(), $user->reveal(), 'fr');
        $handler = new GetContactListViewQueryHandler(
            $userRepository->reveal(),
            $sheetRepository->reveal(),
            $participantInfoGuesser->reveal()
        );
        $result = $handler->handle($query);

        $expected = [
            new ContactPreviewView(
                'Carrie', 'Fisher', 'http://far.away/leia.png', ['New Republic', 'Rebels']
            ),
        ];

        $this->assertEquals($expected, $result);
    }
}
