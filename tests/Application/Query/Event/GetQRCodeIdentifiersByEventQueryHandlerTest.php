<?php

namespace Proximum\Vimeet\Tests\Application\Query\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Event\GetQRCodeIdentifiersByEventQuery;
use Proximum\Vimeet\Application\Query\Event\GetQRCodeIdentifiersByEventQueryHandler;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierListView;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\Type;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class GetQRCodeIdentifiersByEventQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(1337);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('en');
        $event->getAvailableLocale('en')->shouldBeCalled()->willReturn('en');

        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(1);
        $user1->getLocale()->shouldBeCalled()->willReturn('fr');

        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(2);
        $user2->getLocale()->shouldBeCalled()->willReturn('fr');

        $user3 = $this->prophesize(User::class);
        $user3->getId()->shouldBeCalled()->willReturn(3);
        $user3->getLocale()->shouldBeCalled()->willReturn('en');

        $user4 = $this->prophesize(User::class);
        $user4->getId()->shouldBeCalled()->willReturn(4);
        $user4->getLocale()->shouldBeCalled()->willReturn('en');

        $sheet1ParticipantUser1 = $this->prophesize(Participant::class);
        $sheet1ParticipantUser1->getUser()->shouldBeCalled()->willReturn($user1->reveal());

        $sheet1ParticipantUser2 = $this->prophesize(Participant::class);
        $sheet1ParticipantUser2->getUser()->shouldBeCalled()->willReturn($user2->reveal());

        $sheet1ParticipantUser3 = $this->prophesize(Participant::class);
        $sheet1ParticipantUser3->getUser()->shouldBeCalled()->willReturn($user3->reveal());

        $sheet2ParticipantUser4 = $this->prophesize(Participant::class);
        $sheet2ParticipantUser4->getUser()->shouldBeCalled()->willReturn($user4->reveal());

        $sheet2ParticipantUser2 = $this->prophesize(Participant::class);
        $sheet2ParticipantUser2->getUser()->shouldBeCalled()->willReturn($user2->reveal());

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $sheet1ParticipantUser1->reveal(),
                    $sheet1ParticipantUser2->reveal(),
                    $sheet1ParticipantUser3->reveal(),
                ]
            )
        ;

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $sheet2ParticipantUser2->reveal(),
                    $sheet2ParticipantUser4->reveal(),
                ]
            )
        ;

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $date = new \DateTime();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsEnabledByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;

        $checkin = new \DateTime('2018-09-18 08:30:37');

        $scanRepository
            ->getScanDateByUsersAndEvent(
                [
                    1 => $user1->reveal(),
                    2 => $user2->reveal(),
                    3 => $user3->reveal(),
                    4 => $user4->reveal(),
                ],
                $event->reveal(),
                $date
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    2 => new User\Event\Scan(
                        $event->reveal(),
                        $user2->reveal(),
                        $checkin,
                        new \DateTime('2018-09-18 08:40:00'),
                        Type::TYPE_EVENT_ENTRANCE
                    ),
                ]
            )
        ;

        $queryBus->handle(new QRCodeIdentifierQuery($event->reveal(), $user1->reveal()))
            ->shouldBeCalled()
            ->willReturn('00013370000001')
        ;

        $queryBus->handle(new QRCodeIdentifierQuery($event->reveal(), $user2->reveal()))
            ->shouldBeCalled()
            ->willReturn('00013370000002')
        ;

        $queryBus->handle(new QRCodeIdentifierQuery($event->reveal(), $user3->reveal()))
            ->shouldBeCalled()
            ->willReturn('00013370000003')
        ;

        $queryBus->handle(new QRCodeIdentifierQuery($event->reveal(), $user4->reveal()))
            ->shouldBeCalled()
            ->willReturn('00013370000004')
        ;

        $groupNameResolver->resolve($event->reveal(), $user1->reveal(), [$sheet1->reveal()])
            ->shouldBeCalled()
            ->willReturn('France')
        ;

        $groupNameResolver->resolve($event->reveal(), $user2->reveal(), [$sheet1->reveal(), $sheet2->reveal()])
            ->shouldBeCalled()
            ->willReturn('France')
        ;

        $groupNameResolver->resolve($event->reveal(), $user3->reveal(), [$sheet1->reveal()])
            ->shouldBeCalled()
            ->willReturn('France')
        ;

        $groupNameResolver->resolve($event->reveal(), $user4->reveal(), [$sheet2->reveal()])
            ->shouldBeCalled()
            ->willReturn('Croatie')
        ;

        $sheets1 = [$sheet1->reveal()];
        $typeNameResolver
            ->resolveWithPreloadedSheets($sheets1, 'fr')
            ->shouldBeCalled()
            ->willReturn('Groupe A')
        ;

        $sheets1And2 = [$sheet1->reveal(), $sheet2->reveal()];
        $typeNameResolver
            ->resolveWithPreloadedSheets($sheets1And2, 'fr')
            ->shouldBeCalled()
            ->willReturn('Groupe A')
        ;

        $sheets2 = [$sheet2->reveal()];
        $typeNameResolver
            ->resolveWithPreloadedSheets($sheets2, 'fr')
            ->shouldBeCalled()
            ->willReturn('Groupe B')
        ;

        $router = $this->prophesize(RouterInterface::class);
        $router
            ->generate('admin_user_event_badge', ['user' => 1, 'event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event-1337/url/to/badge/1')
        ;
        $router
            ->generate('admin_user_event_badge', ['user' => 2, 'event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event-1337/url/to/badge/2')
        ;
        $router
            ->generate('admin_user_event_badge', ['user' => 3, 'event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event-1337/url/to/badge/3')
        ;
        $router
            ->generate('admin_user_event_badge', ['user' => 4, 'event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event-1337/url/to/badge/4')
        ;

        $router
            ->generate('admin_user_event_planning', ['user' => 1, 'event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event-1337/url/to/planning/1')
        ;
        $router
            ->generate('admin_user_event_planning', ['user' => 2, 'event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event-1337/url/to/planning/2')
        ;
        $router
            ->generate('admin_user_event_planning', ['user' => 3, 'event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event-1337/url/to/planning/3')
        ;
        $router
            ->generate('admin_user_event_planning', ['user' => 4, 'event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event-1337/url/to/planning/4')
        ;

        $userInfoGuesser = $this->prophesize(UserInfoGuesser::class);
        $userInfoGuesser
            ->getUserInfoFromParticipant($user1->reveal(), 'en', [$sheet1->reveal()])
            ->shouldBeCalled()
            ->willReturn(['firstName' => 'Kylian', 'lastName' => 'Mbappe'])
        ;
        $userInfoGuesser
            ->getUserInfoFromParticipant($user2->reveal(), 'en', [$sheet1->reveal(), $sheet2->reveal()])
            ->shouldBeCalled()
            ->willReturn(['firstName' => 'Nikola', 'lastName' => 'Karabatic'])
        ;
        $userInfoGuesser
            ->getUserInfoFromParticipant($user3->reveal(), 'en', [$sheet1->reveal()])
            ->shouldBeCalled()
            ->willReturn(['firstName' => 'Paul', 'lastName' => 'Pogba'])
        ;
        $userInfoGuesser
            ->getUserInfoFromParticipant($user4->reveal(), 'en', [$sheet2->reveal()])
            ->shouldBeCalled()
            ->willReturn(['firstName' => 'Luka', 'lastName' => 'Modric'])
        ;

        $handler = new GetQRCodeIdentifiersByEventQueryHandler(
            $queryBus->reveal(),
            $sheetRepository->reveal(),
            $userInfoGuesser->reveal(),
            $scanRepository->reveal(),
            $groupNameResolver->reveal(),
            $typeNameResolver->reveal(),
            $date,
            $router->reveal()
        );

        static::assertEquals(
            new QRCodeIdentifierListView(
                [
                    new QRCodeIdentifierView(
                        '00013370000001',
                        'Kylian',
                        'Mbappe',
                        'France',
                        'Groupe A',
                        null,
                        '/event-1337/url/to/badge/1',
                        '/event-1337/url/to/planning/1'
                    ),
                    new QRCodeIdentifierView(
                        '00013370000002',
                        'Nikola',
                        'Karabatic',
                        'France',
                        'Groupe A',
                        $checkin,
                        '/event-1337/url/to/badge/2',
                        '/event-1337/url/to/planning/2'
                    ),
                    new QRCodeIdentifierView(
                        '00013370000003',
                        'Paul',
                        'Pogba',
                        'France',
                        'Groupe A',
                        null,
                        '/event-1337/url/to/badge/3',
                        '/event-1337/url/to/planning/3'
                    ),
                    new QRCodeIdentifierView(
                        '00013370000004',
                        'Luka',
                        'Modric',
                        'Croatie',
                        'Groupe B',
                        null,
                        '/event-1337/url/to/badge/4',
                        '/event-1337/url/to/planning/4'
                    ),
                ]
            ),
            $handler->handle(new GetQRCodeIdentifiersByEventQuery($event->reveal(), 'fr'))
        );
    }
}
