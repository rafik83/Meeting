<?php

namespace Proximum\Vimeet\Tests\Application\Query\Networking;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Query\Networking\NetworkingNotAccessibleException;
use Proximum\Vimeet\Application\Query\Networking\GetSnippetQuery;
use Proximum\Vimeet\Application\Query\Networking\GetSnippetQueryHandler;
use Proximum\Vimeet\Application\View\Networking\GetSnippetView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class GetSnippetQueryHandlerTest extends TestCase
{
    public function testNetworkingClosed(): void {

        $notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
        $networkingAccessChecker = $this->prophesize(NetworkingAccessChecker::class);
        $getSnippetQueryHandler = new GetSnippetQueryHandler($notificationSubscriber->reveal(), $networkingAccessChecker->reveal());

        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $getSnippetQuery = new GetSnippetQuery($sheet->reveal(), $user->reveal());

        $this->expectException(NetworkingNotAccessibleException::class);
        $networkingAccessChecker->isSheetAllowedToAccess($sheet->reveal())->shouldBeCalled()->willReturn(false);
        $getSnippetQueryHandler->handle($getSnippetQuery);
    }

    public function testGetSnippetView(): void {

        $notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
        $networkingAccessChecker = $this->prophesize(NetworkingAccessChecker::class);
        $getSnippetQueryHandler = new GetSnippetQueryHandler($notificationSubscriber->reveal(), $networkingAccessChecker->reveal());
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $getSnippetQuery = new GetSnippetQuery($sheet->reveal(), $user->reveal());

        $networkingAccessChecker->isSheetAllowedToAccess($sheet->reveal())->shouldBeCalled()->willReturn(true);
        $notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://www.google.fr');
        $notificationSubscriber->getUserSubscriberKey($sheet->reveal(), $user->reveal())->shouldBeCalled()->willReturn('123456');
        $user->getId()->shouldBeCalled()->willReturn(333);
        $event->getId()->shouldBeCalled()->willReturn(137);
        $notificationSubscriber->getUserTopic(137, 333)->shouldBeCalled()->willReturn('Chat');

        $result = $getSnippetQueryHandler->handle($getSnippetQuery);

        $getSnippetView = new GetSnippetView('http://www.google.fr', '123456', 'Chat');
        self::assertEquals($getSnippetView, $result);

    }
}
