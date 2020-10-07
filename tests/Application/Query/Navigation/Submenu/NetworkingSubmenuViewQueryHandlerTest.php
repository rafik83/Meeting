<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\NetworkingSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\NetworkingSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;


class NetworkingSubmenuViewQueryHandlerTest extends TestCase
{
    public function testHasNoNewChatMessage()
    {
        $sheetId = 1337;

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn($sheetId);

        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

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
        $chatMessageRepository->getMessagesCountByEvent($event->reveal())->shouldBeCalled()->willReturn(0);

        $networkingSubMenuQueryHandler = new NetworkingSubmenuViewQueryHandler($navigationBuilder->reveal(), $accessChecker->reveal(),  $chatMessageRepository->reveal());


        $result = $networkingSubMenuQueryHandler->handle(new NetworkingSubmenuViewQuery(
            $user->reveal(),
            $event->reveal(),
            'fr',
            $sheet->reveal(),
            'event_contact_index'
        ));

        $expectedSubButtonView = new SubmenuButtonView(
            Category::NETWORKING_ICON,
            'navigation.category.networking',
            '/url/to/contacts',
            false,
            null,
            true
        );

        $this->assertEquals($expectedSubButtonView, $result);
    }

    public function testHasNewChatMessage()
    {
        $sheetId = 1337;
        $expectedMessagesCount = 55;

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn($sheetId);

        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        $accessChecker = $this->prophesize(NetworkingAccessChecker::class);
        $accessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $chatMessageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $chatMessageRepository->getMessagesCountByEvent($event->reveal())->shouldBeCalled()->willReturn($expectedMessagesCount);

        $navigationBuilder = $this->prophesize(NavigationBuilderInterface::class);
        $navigationBuilder->getRoute(
            'event_networking_index',
            [
                'sheet' => $sheetId,
            ]
        )->shouldBeCalled()
            ->willReturn('/url/to/contacts');

        $networkingSubMenuQueryHandler = new NetworkingSubmenuViewQueryHandler($navigationBuilder->reveal(), $accessChecker->reveal(), $chatMessageRepository->reveal());


        $result = $networkingSubMenuQueryHandler->handle(new NetworkingSubmenuViewQuery(
            $user->reveal(),
            $event->reveal(),
            'fr',
            $sheet->reveal(),
            'event_contact_index'
        ));

        $expectedSubButtonView = new SubmenuButtonView(
            Category::NETWORKING_ICON,
            'navigation.category.networking',
            '/url/to/contacts',
            false,
            $expectedMessagesCount,
            true
        );

        $this->assertEquals($expectedSubButtonView, $result);
    }
}
