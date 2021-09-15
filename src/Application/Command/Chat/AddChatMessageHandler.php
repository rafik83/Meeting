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
    private $dateTime;

    public function __construct(
        ChatMessageRepositoryInterface $messageRepository,
        CheckAccessToChatMessages $checkAccessToChatMessages,
        NotificationPublisherInterface $notificationPublisher,
        DateTimeInterface $dateTime
    ) {
        $this->messageRepository = $messageRepository;
        $this->checkAccessToChatMessages = $checkAccessToChatMessages;
        $this->notificationPublisher = $notificationPublisher;
        $this->dateTime = $dateTime;
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
            $this->dateTime,
            $command->content,
            $command->user->getFullname(),
            $command->sheet->getTitle()
        ));

        if ($command->object instanceof ChatSession) {
            $command->object->incrementUnreadMessages($command->object->getOtherUser($command->user));
        }

        $messageCount = $this->messageRepository->getMessagesCountByLinkableObject($command->object, null);
        $this->notificationPublisher->publishChatMessageNotification($command->object, $message, $messageCount);
    }
}
