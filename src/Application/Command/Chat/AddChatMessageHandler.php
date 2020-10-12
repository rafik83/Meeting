<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class AddChatMessageHandler
{
    /** @var ChatMessageRepositoryInterface */
    private $messageRepository;

    /** @var CheckAccessToChatMessages */
    private $checkAccessToChatMessages;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    /** @var DateTimeInterface */
    private $now;

    public function __construct(
        ChatMessageRepositoryInterface $messageRepository,
        CheckAccessToChatMessages $checkAccessToChatMessages,
        NotificationPublisherInterface $notificationPublisher,
        DateTimeInterface $now
    ) {
        $this->messageRepository = $messageRepository;
        $this->checkAccessToChatMessages = $checkAccessToChatMessages;
        $this->notificationPublisher = $notificationPublisher;
        $this->now = $now;
    }

    /**
     * @throws ChatMessageNotAllowedException
     */
    public function handle(AddChatMessage $command): void
    {
        if (!$this->checkAccessToChatMessages->isSatisfiedBy($command->object, $command->user)) {
            throw new ChatMessageNotAllowedException('Access denied to this chat messages');
        }

        $message = $this->messageRepository->add(new ChatMessage(
            $command->object,
            $command->user,
            $this->now,
            $command->content,
            $command->user->getFullname(),
            $command->sheet->getTitle()
        ));

        if ($command->object instanceof ChatSession) {
            $command->object->incrementUnreadMessages();
        }

        $this->notificationPublisher->publishChatMessageNotification($command->object, $message);
    }
}
