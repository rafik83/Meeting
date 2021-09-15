<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Command\Chat\NotificationType;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

interface NotificationPublisherInterface
{
    public function publishHappeningNotification(Happening $happening, string $type, array $data): void;

    public function publishChatMessageNotification(
        ChatMessageLinkableInterface $object,
        ChatMessage $message,
        int $messageCount,
        string $action = NotificationType::DELETE_CHAT_MESSAGE
    ): void;

    public function publishChatVoteNotification(ChatMessageLinkableInterface $object, ChatMessage $chatMessage, array $votes): void;

    public function publishUserConnectionNotification(Sheet $sheet, User $user): void;

    public function publishRequestVisioNotification(Sheet $sheet, User $fromUser, int $toUserId, string $type): void;

    public function publishDelayedPollVoteNotification(Happening\Poll $poll): void;

    public function publishedPollVoteNotification(Happening\Poll $poll): void;

    public function publishNewPublishedPollNotification(Happening\Poll $poll): void;

    public function publishHiddenPollNotification(Happening\Poll $poll): void;
}
