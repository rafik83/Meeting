<?php

namespace Proximum\Vimeet\Tests\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Query\Networking\ClosedNetworkingException;
use Proximum\Vimeet\Application\Query\Networking\GetSnippetQuery;
use Proximum\Vimeet\Application\Query\Networking\GetSnippetQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Networking\GetSnippetView;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetSnippetQueryHandlerTest extends TestCase
{
    public function testNetworkingClosed(): void {

        $notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
        $networkingAccessChecker = $this->prophesize(NetworkingAccessChecker::class);
        $getSnippetQueryHandler = new GetSnippetQueryHandler($notificationSubscriber->reveal(), $networkingAccessChecker->reveal());
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $getSnippetQuery = new GetSnippetQuery($event->reveal(), $user->reveal());
        $this->expectException(ClosedNetworkingException::class);
        $networkingAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(false);
        $getSnippetQueryHandler->handle($getSnippetQuery);
    }

    public function testGetSnippetView(): void {

        $notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
        $networkingAccessChecker = $this->prophesize(NetworkingAccessChecker::class);
        $getSnippetQueryHandler = new GetSnippetQueryHandler($notificationSubscriber->reveal(), $networkingAccessChecker->reveal());
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $getSnippetQuery = new GetSnippetQuery($event->reveal(), $user->reveal());

        $networkingAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://www.google.fr');
        $notificationSubscriber->getEventSubscriberKey($event->reveal(), $user->reveal())->shouldBeCalled()->willReturn('123456');
        $user->getId()->shouldBeCalled()->willReturn(333);
        $notificationSubscriber->getChatSessionTopic(333)->shouldBeCalled()->willReturn('Chat');

        $result = $getSnippetQueryHandler->handle($getSnippetQuery);

        $getSnippetView = new GetSnippetView('http://www.google.fr', '123456', 'Chat');
        self::assertEquals($getSnippetView, $result);

    }
}
