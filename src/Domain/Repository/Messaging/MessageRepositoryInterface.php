<?php

namespace Proximum\Vimeet\Domain\Repository\Messaging;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Messaging\MessageTranslation;

interface MessageRepositoryInterface
{
    /**
     * Inserts a new message.
     *
     * @param Message $message
     */
    public function add(Message $message);

    /**
     * Updates a given message.
     *
     * @param Message $message
     */
    public function set(Message $message);

    /**
     * Finds all messages for a given event.
     *
     * @param Event $event
     *
     * @return Message[]
     */
    public function findByEvent(Event $event);

    /**
     * Finds all messages for a given event order by name.
     *
     * @param Event $event
     *
     * @return Message[]
     */
    public function findByEventOrderByName(Event $event): array;

    /**
     * @param MessageTranslation $messageTranslation
     */
    public function removeTranslation(MessageTranslation $messageTranslation);
}
