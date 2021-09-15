<?php

namespace Proximum\Vimeet\Domain\Event\Message;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;

class Duplicator
{
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param MessageRepositoryInterface $messageRepository
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        MessageRepositoryInterface $messageRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->messageRepository = $messageRepository;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param Event $event
     */
    public function duplicate(Event $event)
    {
        $messages = $this->messageRepository->findByEvent($event->getDuplicatedFrom());

        foreach ($messages as $message) {
            $newMessage = new Message(
                $event,
                $this->dateTime,
                $message->getName(),
                $message->isSendToEmailTeam(),
                $message->isSendEmailToBillingInfo()
            );

            foreach ($event->getLocales() as $locale) {
                $newMessage->translate(
                    $locale,
                    $message->getSubject($locale),
                    $message->getContent($locale),
                    $this->dateTime
                );
            }

            $this->messageRepository->add($newMessage);
        }
    }
}
