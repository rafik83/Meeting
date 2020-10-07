<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Application\Query\Chat\View\ChatMessageView;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Event;

interface ChatMessageRepositoryInterface
{
    public function add(ChatMessage $chatMessage): ChatMessage;

    /**
     * @return ChatMessageView[]
     */
    public function list(ChatMessageLinkableInterface $object): array;

    public function findById(int $id): ?ChatMessage;
    public function getMessagesCountByEvent(Event $event): int;
}
