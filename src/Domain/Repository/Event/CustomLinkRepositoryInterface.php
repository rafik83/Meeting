<?php

namespace Proximum\Vimeet\Domain\Repository\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

interface CustomLinkRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return Event\CustomLink[]
     */
    public function findByEvent(Event $event): array;

    public function add(Event\CustomLink $customLink): void;

    public function remove(Event\CustomLink $customLink): void;

    public function set(Event\CustomLink $customLink): void;

    /**
     * @param Type $type
     *
     * @return Event\CustomLink[]
     */
    public function findByType(Type $type): array;
}
