<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
        $command->message->update($command->associatedTypes);
        $command->message->updateTranslations($command->translations);

        $this->messageRepository->update($command->message);
    }
}
