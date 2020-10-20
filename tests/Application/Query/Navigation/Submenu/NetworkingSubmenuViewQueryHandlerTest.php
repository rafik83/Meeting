<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\NetworkingSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\NetworkingSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;


class NetworkingSubmenuViewQueryHandlerTest extends TestCase
{
    public function testHasNoNewChatMessage()
    {
        $sheetId = 1337;

        $queryRoute = "NOT_NETWORKING_ROUTE";

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn($sheetId);

        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($this->prophesize(Participant::class));

        $accessChecker = $this->prophesize(NetworkingAccessChecker::class);
        $accessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $navigationBuilder->getRoute(
            'event_networking_index',
            [
                'sheet' => $sheetId,
            ]
        )->shouldBeCalled()
            ->willReturn('/url/to/contacts');

        $chatMessageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $chatMessageRepository->getMessagesCountByEvent($event->reveal(), null)->shouldBeCalled()->willReturn(0);

        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);
        $chatSessionRepository->findSessionsByEventAndUser($event->reveal(), $user->reveal())->shouldBeCalled()->willReturn([]);

        $networkingSubMenuQueryHandler = new NetworkingSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $accessChecker->reveal(),
            $chatMessageRepository->reveal(),
            $chatSessionRepository->reveal()
        );
        $expectedAttributes =  ['data-is-networking-page-active' => false];

        $expectedSubButtonView = new SubmenuButtonView(
            Category::NETWORKING_ICON,
            'navigation.category.networking',
            '/url/to/contacts',
            false,
            0,
            true,
            $expectedAttributes
        );

        $result = $networkingSubMenuQueryHandler->handle(new NetworkingSubmenuViewQuery(
            $user->reveal(),
            $event->reveal(),
            'fr',
            $sheet->reveal(),
            $queryRoute
        ));

        $this->assertEquals($result, $expectedSubButtonView);
    }

    public function testHasNewChatMessage()
    {
        $sheetId = 1337;
        $expectedMessagesCount = 53 + 2; // networking chat + private chat

        $queryRoute = "NOT_NETWORKING_ROUTE";

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn($sheetId);

        $lastViewDate = new \DateTime('2020-03-14T03:14:15');

        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(31415);
        $event = $this->prophesize(Event::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getNetworkingChatViewedAt()->shouldBeCalled()->willReturn($lastViewDate);
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant->reveal());

        $accessChecker = $this->prophesize(NetworkingAccessChecker::class);
        $accessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $chatMessageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $chatMessageRepository->getMessagesCountByEvent($event->reveal(), $lastViewDate)
            ->shouldBeCalled()
            ->willReturn(53);

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $navigationBuilder->getRoute(
            'event_networking_index',
            [
                'sheet' => $sheetId,
            ]
        )->shouldBeCalled()
            ->willReturn('/url/to/contacts');

        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);
        $chatSessionRepository->findSessionsByEventAndUser($event->reveal(), $user->reveal())->shouldBeCalled()->willReturn([['unreadMessages' => [31415 => 2]]]);

        $networkingSubMenuQueryHandler = new NetworkingSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $accessChecker->reveal(),
            $chatMessageRepository->reveal(),
            $chatSessionRepository->reveal()
        );

        $expectedAttributes =  ['data-is-networking-page-active' => false];

        $expectedSubButtonView = new SubmenuButtonView(
            Category::NETWORKING_ICON,
            'navigation.category.networking',
            '/url/to/contacts',
            false,
            $expectedMessagesCount,
            true,
            $expectedAttributes
        );

        $result = $networkingSubMenuQueryHandler->handle(new NetworkingSubmenuViewQuery(
            $user->reveal(),
            $event->reveal(),
            'fr',
            $sheet->reveal(),
            $queryRoute
        ));

        $this->assertEquals($result, $expectedSubButtonView);
    }

    public function testIsInNetworkingRoute()
    {
        $event = $this->prophesize(Event::class);

        $accessChecker = $this->prophesize(NetworkingAccessChecker::class);
        $accessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $queryRoute = Route::NETWORKING;

        $user = $this->prophesize(User::class);
        $user->getId()->shouldNotBeCalled();

        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);
        $chatSessionRepository->findSessionsByEventAndUser($event->reveal(), $user->reveal())->shouldNotBeCalled();

        $sheetId = 1337;
        $expectedMessagesCount = 0;

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn($sheetId);

        $lastViewDate = new \DateTime('2020-03-14T03:14:15');

        $chatMessageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $chatMessageRepository->getMessagesCountByEvent($event->reveal(), $lastViewDate)
            ->shouldNotBeCalled();


        $dummyUrl = "/dummy/url/";


        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $navigationBuilder->getRoute(
            'event_networking_index',
            [
                'sheet' => $sheetId,
            ]
        )->shouldBeCalled()
            ->willReturn($dummyUrl);



        $networkingSubMenuQueryHandler = new NetworkingSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $accessChecker->reveal(),
            $chatMessageRepository->reveal(),
            $chatSessionRepository->reveal()
        );

        $expectedAttributes =  ['data-is-networking-page-active' => true];

        $expectedSubButtonView = new SubmenuButtonView(
            Category::NETWORKING_ICON,
            'navigation.category.networking',
            $dummyUrl,
            true,
            $expectedMessagesCount,
            true,
            $expectedAttributes
        );

        $result = $networkingSubMenuQueryHandler->handle(new NetworkingSubmenuViewQuery(
            $user->reveal(),
            $event->reveal(),
            'fr',
            $sheet->reveal(),
            $queryRoute
        ));

        $this->assertEquals($result, $expectedSubButtonView);
    }
}
