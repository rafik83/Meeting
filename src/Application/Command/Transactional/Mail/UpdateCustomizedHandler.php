<?php

namespace Proximum\Vimeet\Application\Command\Transactional\Mail;

use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;

class UpdateCustomizedHandler
{
    /** @var MessageRepositoryInterface */
    private $messageRepository;

    public function __construct(MessageRepositoryInterface $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function handle(UpdateCustomized $command): void
    {
        $command->message->update($command->associatedTypes, $command->enabled);
        $command->message->updateTranslations($command->translations);

        $this->messageRepository->update($command->message);
    }
}
