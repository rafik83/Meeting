<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Messaging;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;

class MessageManager
{
    /** @var MessageRepositoryInterface */
    private $messageRepository;

    public function __construct(MessageRepositoryInterface $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function create(
        Event $event,
        string $name
    ): Message {
        $date = new \DateTime();
        $message = new Message($event, $date, $name);

        foreach ($event->getLocales() as $locale) {
            $message->translate(
                $locale,
                "subect_$locale",
                "content_$locale",
                $date
            );
        }

        $this->messageRepository->add($message);

        return $message;
    }
}
