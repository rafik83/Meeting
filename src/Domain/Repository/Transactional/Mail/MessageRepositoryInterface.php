<?php

namespace Proximum\Vimeet\Domain\Repository\Transactional\Mail;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Model\Type;

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
     * @param Event  $event
     * @param string $transactionalMailType
     *
     * @return Message|null
     */
    public function getOneByEventAndType(Event $event, string $transactionalMailType): ?Message;

    /**
     * @param Event  $event
     * @param string $transactionalMailType
     * @param Type   $associatedType
     *
     * @return Message|null
     */
    public function getOneByEventAndTypeAndAssociatedType(
        Event $event,
        string $transactionalMailType,
        Type $associatedType
    ): ?Message;

    /**
     * @param Event $event
     *
     * @return Message[]
     */
    public function findByEvent(Event $event): array;

    public function remove(Message $message): void;
}
