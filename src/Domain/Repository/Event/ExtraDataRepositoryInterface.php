<?php

namespace Proximum\Vimeet\Domain\Repository\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;

interface ExtraDataRepositoryInterface
{
    /**
     * @param ExtraData $extraData
     */
    public function add(ExtraData $extraData);

    /**
     * @param ExtraData $extraData
     */
    public function set(ExtraData $extraData);

    /**
     * @param Event  $event
     * @param string $name
     *
     * @return null|ExtraData
     */
    public function getExtraDataForEvent(Event $event, string $name): ?ExtraData;

    /**
     * @param int $extraDataId
     *
     * @return null|ExtraData
     */
    public function findById(int $extraDataId): ?ExtraData;
}
