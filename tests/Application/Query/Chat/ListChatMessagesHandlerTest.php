<?php

namespace Proximum\Vimeet\Tests\Application\Query\Chat;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Query\Chat\ListChatMessages;
use Proximum\Vimeet\Application\Query\Chat\ListChatMessagesHandler;
use Proximum\Vimeet\Application\Query\Chat\View\ChatMessageView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class ListChatMessagesHandlerTest extends TestCase
{
    /** @var ObjectProphecy|User */
    private $user;
    /** @var ObjectProphecy|Happening */
    private $happening;
    /** @var ObjectProphecy|ChatMessageRepositoryInterface */
    private $chatMessageRepository;
    /** @var ObjectProphecy|CheckAccessToChatMessages */
    private $checkAccessToChatMessages;
    /** @var ObjectProphecy|GetTimezoneHelper */
    private $getTimezoneHelper;
    /** @var ObjectProphecy */
    private $routerAdapter;
    /** @var ListChatMessagesHandler */
    private $listChatMessagesHandler;

    protected function setUp()
    {
        $this->user = $this->prophesize(User::class);
        $this->happening = $this->prophesize(Happening::class);

        $this->chatMessageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $this->checkAccessToChatMessages = $this->prophesize(CheckAccessToChatMessages::class);
        $this->getTimezoneHelper = $this->prophesize(GetTimezoneHelper::class);
        $this->routerAdapter = $this->prophesize(RouterInterface::class);

        $this->listChatMessagesHandler = new ListChatMessagesHandler(
            $this->chatMessageRepository->reveal(),
            $this->checkAccessToChatMessages->reveal(),
            $this->getTimezoneHelper->reveal(),
            $this->routerAdapter->reveal()
        );
    }

    public function test_access_denied()
    {
        $this->expectException(ChatMessageNotAllowedException::class);

        $this->checkAccessToChatMessages
            ->isSatisfiedBy($this->happening->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->listChatMessagesHandler->handle(
            new ListChatMessages($this->happening->reveal(), $this->user->reveal(), 'fr')
        );
    }

    public function test_get_messages()
    {
        $this->checkAccessToChatMessages
            ->isSatisfiedBy($this->happening, $this->user)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->chatMessageRepository->list($this->happening->reveal())->shouldBeCalled()->willReturn(
            [
                new ChatMessageView(1, 'Hello!', new \DateTime('2020-05-05 12:00:00'), '/custom-picture.jpg', 12345, 'Pierre DUPONT.', 'Taxi Inc.'),
                new ChatMessageView(2, 'How are you?', new \DateTime('2020-05-05 12:05:00'), null, 007, 'Leeloo', 'Fifth Element'),
            ]
        );

        $event = $this->prophesize(Event::class);
        $this->happening->getEvent()->shouldBeCalled()->willReturn($event);
        $this->getTimezoneHelper
            ->getTimezoneByEventAndUser($event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn('Europe/Paris')
        ;

        $this->routerAdapter->generate('event_chat_avatar', Argument::withKey('name'))
            ->shouldBeCalled()
            ->willReturn('/fr/chat/avatar?Leeloo');

        $chatMessageViews = $this->listChatMessagesHandler->handle(
            new ListChatMessages($this->happening->reveal(), $this->user->reveal(), 'fr')
        );

        $result1 = new ChatMessageView(1, 'Hello!', new \DateTime('2020-05-05 12:00:00'), '/custom-picture.jpg', 12345, 'Pierre DUPONT.', 'Taxi Inc.');
        $result1->formattedCreatedAt = '14:00:00';

        $result2 = new ChatMessageView(2, 'How are you?', new \DateTime('2020-05-05 12:05:00'), '/fr/chat/avatar?Leeloo', 007, 'Leeloo', 'Fifth Element');
        $result2->formattedCreatedAt = '14:05:00';

        $this->assertEquals([$result1, $result2], $chatMessageViews);
    }
}
