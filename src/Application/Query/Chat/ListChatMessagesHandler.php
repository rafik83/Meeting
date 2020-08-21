<?php

namespace Proximum\Vimeet\Application\Query\Chat;

use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Query\Chat\Exception\AccessDeniedToChatMessages;
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

    public function __construct(
        ChatMessageRepositoryInterface $chatMessageRepository,
        CheckAccessToChatMessages $checkAccessToChatMessages,
        GetTimezoneHelper $getTimezoneHelper
    ) {
        $this->chatMessageRepository = $chatMessageRepository;
        $this->checkAccessToChatMessages = $checkAccessToChatMessages;
        $this->getTimezoneHelper = $getTimezoneHelper;
    }

    /**
     * @return ChatMessageView[]
     * @throws AccessDeniedToChatMessages
     */
    public function handle(ListChatMessages $query): array
    {
        if (!$this->checkAccessToChatMessages->isSatisfiedBy($query->object, $query->user)) {
            throw new AccessDeniedToChatMessages('Access denied to this chat messages');
        }

        $chatMessagesViews = $this->chatMessageRepository->list($query->object);

        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndUser($query->object->getEvent(), $query->user);
        $mediumHourFormatter = DayHelper::getMediumHourFormatter($query->locale, $timezone);

        foreach ($chatMessagesViews as $chatMessagesView) {
            $chatMessagesView->formattedCreatedAt = $mediumHourFormatter->format($chatMessagesView->createdAt);
        }

        return $chatMessagesViews;
    }
}
