<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Transactional\Mail;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;

interface MessageRepositoryInterface
{
    public function add(Message $message): void;

    public function update(Message $message): void;

    /**
     * @param Event  $event
     * @param string $transactionalMailType
     *
     * @return Message[]
     */
    public function findByEventAndType(Event $event, string $transactionalMailType): array;

    /**
     * @param Event $event
     *
     * @return Message[]
     */
    public function findByEvent(Event $event): array;

    public function remove(Message $message): void;
}
