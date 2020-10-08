<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

interface NotificationPublisherInterface
{
    public function publishHappeningNotification(Happening $happening, string $type, array $data): void;

    public function publishChatMessageNotification(ChatMessageLinkableInterface $object, ChatMessage $message): void;

    public function publishChatVoteNotification(ChatMessageLinkableInterface $object, ChatMessage $chatMessage, array $votes): void;

    public function publishUserConnectionNotification(Event $event, User $user): void;
}
