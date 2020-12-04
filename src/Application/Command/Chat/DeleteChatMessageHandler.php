<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Chat\DeleteChatMessage;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotFoundException;
use Proximum\Vimeet\Application\Exception\Chat\DeleteChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObject;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObjectHandler;
use Proximum\Vimeet\Domain\Chat\CanDeleteChatMessage;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class DeleteChatMessageHandler
{
    /** @var ChatMessageRepositoryInterface */
    private $messageRepository;

    /** @var GuessChatMessageLinkableObjectHandler */
    private $guessChatMessageLinkableObjectHandler;

    /** @var CanDeleteChatMessage */
    private $canDeleteChatMessage;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    public function __construct(
        ChatMessageRepositoryInterface $messageRepository,
        GuessChatMessageLinkableObjectHandler $guessChatMessageLinkableObjectHandler,
        CanDeleteChatMessage $canDeleteChatMessage,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->messageRepository = $messageRepository;
        $this->guessChatMessageLinkableObjectHandler = $guessChatMessageLinkableObjectHandler;
        $this->canDeleteChatMessage = $canDeleteChatMessage;
        $this->notificationPublisher = $notificationPublisher;
    }

    /**
     * @throws ChatMessageNotAllowedException
     */
    public function handle(DeleteChatMessage $command): void
    {
        $message = $this->messageRepository->findById($command->messageId);
        if (null === $message) {
            throw new ChatMessageNotFoundException(sprintf('Message with ID #%d not found', $command->messageId));
        }

        /** @var ChatMessageLinkableInterface $object */
        $context = $this->guessChatMessageLinkableObjectHandler->handle(
            new GuessChatMessageLinkableObject($message->getObjectType(), $message->getObjectId())
        );

        if ($command->event->getId() !== $context->getEvent()->getId()) {
            throw new ChatMessageNotAllowedException('Object not in this event');
        }

        if (!$this->canDeleteChatMessage->isSatisfiedBy($context, $command->user)) {
            throw new DeleteChatMessageNotAllowedException(sprintf('Delete message #%d is not allowed for this user', $command->messageId));
        }

        $messageCount = $this->messageRepository->getMessagesCountByLinkableObject($context, null);
        $this->notificationPublisher->publishChatMessageNotification($context, $message, $messageCount, 'delete_chat_message');

        $this->messageRepository->delete($message);
    }
}
