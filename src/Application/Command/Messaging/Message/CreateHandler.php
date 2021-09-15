<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Message;

use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;

class CreateHandler
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
    public function __construct(MessageRepositoryInterface $messageRepository, \DateTimeInterface $dateTime)
    {
        $this->messageRepository = $messageRepository;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param Create $command
     */
    public function handle(Create $command)
    {
        $message = new Message($command->getEvent(), $this->dateTime, $command->name);

        foreach ($command->translations as $locale => $translation) {
            $message->translate($locale, $translation['subject'], $translation['content'], $this->dateTime);
        }

        $this->messageRepository->add($message);
    }
}
