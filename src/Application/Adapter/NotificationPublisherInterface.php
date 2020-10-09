<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

interface NotificationPublisherInterface
{
    public function publishHappeningNotification(Happening $happening, string $type, array $data): void;

    public function publishChatMessageNotification(ChatMessageLinkableInterface $object): void;

    public function publishChatVoteNotification(ChatMessageLinkableInterface $object, int $chatMessageId, array $votes): void;

    public function publishUserConnectionNotification(Sheet $sheet, User $user): void;
}
