<?php

namespace Proximum\Vimeet\Domain\Repository\Event;

use Proximum\Vimeet\Domain\Model\Event;

interface ExtraParameterRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return Event\ExtraParameter[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event\ExtraParameter $extraParameter
     */
    public function set(Event\ExtraParameter $extraParameter);

    /**
     * @param Event\ExtraParameter $extraParameter
     */
    public function add(Event\ExtraParameter $extraParameter);

    /**
     * @param Event\ExtraParameter $extraParameter
     */
    public function remove(Event\ExtraParameter $extraParameter);

    /**
     * @param Event  $event
     * @param string $type
     *
     * @return null|Event\ExtraParameter
     */
    public function findByEventAndType(Event $event, string $type): ?Event\ExtraParameter;
}
