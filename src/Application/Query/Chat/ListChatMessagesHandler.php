<?php

namespace Proximum\Vimeet\Application\Query\Chat;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Query\Chat\View\ChatMessageView;
use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class ListChatMessagesHandler
{
    /** @var ChatMessageRepositoryInterface */
    private $chatMessageRepository;

    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    /** @var CheckAccessToChatMessages */
    private $checkAccessToChatMessages;

    /** @var RouterInterface */
    private $routerAdapter;

    public function __construct(
        ChatMessageRepositoryInterface $chatMessageRepository,
        CheckAccessToChatMessages $checkAccessToChatMessages,
        GetTimezoneHelper $getTimezoneHelper,
        RouterInterface $routerAdapter
    ) {
        $this->chatMessageRepository = $chatMessageRepository;
        $this->checkAccessToChatMessages = $checkAccessToChatMessages;
        $this->getTimezoneHelper = $getTimezoneHelper;
        $this->routerAdapter = $routerAdapter;
    }

    /**
     * @return ChatMessageView[]
     * @throws ChatMessageNotAllowedException
     */
    public function handle(ListChatMessages $query): array
    {
        if (!$this->checkAccessToChatMessages->isSatisfiedBy($query->object, $query->user)) {
            throw new ChatMessageNotAllowedException('Access denied to this chat messages');
        }

        $chatMessagesViews = $this->chatMessageRepository->list($query->object);

        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndUser($query->object->getEvent(), $query->user);
        $mediumHourFormatter = DayHelper::getMediumHourFormatter($query->locale, $timezone);

        foreach ($chatMessagesViews as $chatMessagesView) {
            $chatMessagesView->formattedCreatedAt = $mediumHourFormatter->format($chatMessagesView->createdAt);
            $chatMessagesView->isAuthor = $query->user->getId() === $chatMessagesView->authorId;
            if (null === $chatMessagesView->avatar) {
                $chatMessagesView->avatar = $this->routerAdapter->generate('event_chat_avatar', ['name' => $chatMessagesView->authorName]);
            }
        }

        return $chatMessagesViews;
    }
}
