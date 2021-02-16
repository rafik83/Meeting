<?php

namespace Proximum\Vimeet\Domain\Repository\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

interface MassRepositoryInterface
{
    /**
     * @param Mass $mass
     */
    public function create(Mass $mass);

    /**
     * @param Mass $mass
     */
    public function update(Mass $mass);

    /**
     * @param Event       $event
     * @param string|null $locale
     *
     * @return Mass[]
     */
    public function findByEvent(Event $event, $locale = null);

    /**
     * @deprecated use findByTypes
     */
    public function findByType(Type $type, string $locale);

    /**
     * @param Type[] $types
     * @param string $locale
     *
     * @return Mass[]
     */
    public function findByTypes(array $types, string $locale): array;

    /**
     * @param Event $event
     *
     * @return Mass[]
     */
    public function findDispatchByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Mass[]
     */
    public function findBlockingByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Mass[]
     */
    public function findNotDispatchedByEvent(Event $event);

    /**
     * @param Mass $mass
     */
    public function remove(Mass $mass);
}
