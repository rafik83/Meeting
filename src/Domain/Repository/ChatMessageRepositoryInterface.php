<?php

namespace Proximum\Vimeet\Domain\Repository;

use DateTimeInterface;
use Proximum\Vimeet\Application\Query\Chat\View\ChatMessageView;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;

interface ChatMessageRepositoryInterface
{
    public function add(ChatMessage $chatMessage): ChatMessage;

    /** @return ChatMessageView[] */
    public function list(ChatMessageLinkableInterface $object): array;

    public function findById(int $id): ?ChatMessage;

    public function getMessagesCountByLinkableObject(ChatMessageLinkableInterface $object, ?DateTimeInterface $viewedAfter): int;
}
