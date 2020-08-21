<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class AddChatMessageHandler
{
    /** @var ChatMessageRepositoryInterface */
    private $messageRepository;

    /** @var \DateTime */
    private $now;

    public function __construct(ChatMessageRepositoryInterface $messageRepository, \DateTimeInterface $now)
    {
        $this->messageRepository = $messageRepository;
        $this->now = $now;
    }

    public function handle(AddChatMessage $command): void
    {
        var_dump($this->now);
        $this->messageRepository->add(new ChatMessage($command->object, $command->user, $this->now, $command->content));
    }
}
