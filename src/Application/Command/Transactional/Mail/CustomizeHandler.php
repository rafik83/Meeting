<?php

namespace Proximum\Vimeet\Application\Command\Transactional\Mail;

use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;

class CustomizeHandler
{
    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->messageRepository = $messageRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Customize $command): void
    {
        $message = new Message(
            $command->event,
            $command->transactionalMailType,
            $this->dateTime,
            $command->enabled,
            $command->associatedTypes
        );

        foreach ($command->translations as $locale => $translation) {
            $message->translate($locale, $translation['subject'], $translation['content']);
        }

        $this->messageRepository->add($message);
    }
}
