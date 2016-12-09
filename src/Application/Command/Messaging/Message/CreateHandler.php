<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
    private $date;

    /**
     * @param MessageRepositoryInterface $messageRepository
     * @param \DateTimeInterface         $date
     */
    public function __construct(MessageRepositoryInterface $messageRepository, \DateTimeInterface $date)
    {
        $this->messageRepository = $messageRepository;
        $this->date = $date;
    }

    /**
     * @param Create $command
     */
    public function handle(Create $command)
    {
        $this->messageRepository->add(new Message($command->getEvent(), $this->date, $command->name, $command->subject, $command->content));
    }
}
