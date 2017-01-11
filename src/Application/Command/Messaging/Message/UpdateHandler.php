<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Message;

use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;

class UpdateHandler
{
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(MessageRepositoryInterface $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param Update $command
     */
    public function handle(Update $command)
    {
        $message = $command->getMessage();
        $message->update($command->name, $command->subject, $command->content);

        $this->messageRepository->set($message);
    }
}
