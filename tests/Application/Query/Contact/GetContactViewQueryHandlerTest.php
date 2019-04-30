<?php

namespace Proximum\Vimeet\Tests\Application\Query\Contact;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Contact\ContactSheetView;
use Proximum\Vimeet\Application\Query\Contact\ContactView;
use Proximum\Vimeet\Application\Query\Contact\GetContactViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetContactViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ObjectProphecy|RouterInterface */
    private $router;

    /** @var ObjectProphecy|SheetRepositoryInterface */
    private $sheetRepository;

    /** @var GetContactViewQueryHandler */
    private $getContactViewQueryHandler;

    /** @var ObjectProphecy|Event */
    private $event;

    /** @var ObjectProphecy|Sheet */
    private $userSheet;

    /** @var ObjectProphecy|User */
    private $contact;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->userSheet = $this->prophesize(Sheet::class);
        $this->contact = $this->prophesize(User::class);

        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $this->getContactViewQueryHandler = new GetContactViewQueryHandler(
            $this->participantInfoGuesser->reveal(),
            $this->router->reveal(),
            $this->sheetRepository->reveal()
        );
    }

    public function testHandle()
    {
        $this->userSheet->getId()->shouldBeCalled()->willReturn(1337);
        $participantOfContact = $this->prophesize(Participant::class);

        $contactSheet1 = $this->prophesize(Sheet::class);
        $contactSheet1->getUserParticipant($this->contact->reveal())->shouldBeCalled()->willReturn(null);
        $contactSheet1->getTitle()->shouldBeCalled()->willReturn('My first sheet');
        $contactSheet1->getId()->shouldBeCalled()->willReturn(1);

        $contactSheet2 = $this->prophesize(Sheet::class);
        $contactSheet2->getTitle()->shouldBeCalled()->willReturn('My second sheet');
        $contactSheet2->getId()->shouldBeCalled()->willReturn(2);
        $contactSheet2
            ->getUserParticipant($this->contact->reveal())
            ->shouldBeCalled()
            ->willReturn($participantOfContact->reveal())
        ;

        $this->router
            ->generate(
                'event_catalog_complete_sheet',
                [
                    'sheet' => 1337, 'sheetToDisplay' => 1,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('/sheet/1')
        ;

        $this->router
            ->generate(
                'event_catalog_complete_sheet',
                [
                    'sheet' => 1337, 'sheetToDisplay' => 2,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('/sheet/2')
        ;

        $this->participantInfoGuesser
            ->guessParticipantInfos($participantOfContact, 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    Tag::PARTICIPANT_FIRSTNAME => 'Korben',
                    Tag::PARTICIPANT_LASTNAME => 'Dallas',
                    Tag::PARTICIPANT_POSITION => 'Taxi driver',
                    Tag::PARTICIPANT_AVATAR => '/korben.bmp',
                ]
            )
        ;

        $this->sheetRepository
            ->getSheetsByUserAndEvent($this->contact->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$contactSheet1->reveal(), $contactSheet2->reveal()])
        ;

        $this->assertEquals(
            new ContactView(
                'Korben',
                'Dallas',
                'Taxi driver',
                '/korben.bmp',
                [
                    new ContactSheetView('My first sheet', '/sheet/1'),
                    new ContactSheetView('My second sheet', '/sheet/2'),
                ]
            ),
            $this->getContactViewQueryHandler->handle(
                new GetContactViewQuery(
                    $this->event->reveal(),
                    $this->userSheet->reveal(),
                    $this->contact->reveal(),
                    'fr'
                )
            )
        );
    }
}
