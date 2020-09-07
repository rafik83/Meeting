<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\Happening;

interface NotificationPublisherInterface
{
    public function publishHappeningNotification(Happening $happening, string $type, array $data): void;
    public function publishChatMessageNotification(ChatMessageLinkableInterface $object, int $chatMessageId): void;
}
