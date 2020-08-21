<?php

namespace Proximum\Vimeet\Application\Query\Chat;

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

    public function __construct(
        ChatMessageRepositoryInterface $chatMessageRepository,
        GetTimezoneHelper $getTimezoneHelper
    ) {
        $this->chatMessageRepository = $chatMessageRepository;
        $this->getTimezoneHelper = $getTimezoneHelper;
    }

    /**
     * @return ChatMessageView[]
     */
    public function handle(ListChatMessages $query): array
    {
        $chatMessagesViews = $this->chatMessageRepository->list($query->object);

        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndUser($query->object->getEvent(), $query->user);
        $mediumHourFormatter = DayHelper::getMediumHourFormatter($query->locale, $timezone);

        foreach ($chatMessagesViews as $chatMessagesView) {
            $chatMessagesView->formattedCreatedAt = $mediumHourFormatter->format($chatMessagesView->createdAt);
        }

        return $chatMessagesViews;
    }
}
