<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Message;

use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;

class UpdateHandler
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
     * @param Update $command
     */
    public function handle(Update $command)
    {
        $message = $command->getMessage();

        foreach ($command->translations as $locale => $translation) {
            $message->translate(
                $translation['locale'],
                $translation['subject'],
                $translation['content'],
                $this->dateTime
            );
        }

        $this->messageRepository->set($message->update($command->name));
    }
}
