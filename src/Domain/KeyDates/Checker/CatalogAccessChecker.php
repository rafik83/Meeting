<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;

class CatalogAccessChecker extends AccessChecker
{
    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        if (null === $event->getConfiguration()->getCatalogOnlineDate()) {
            return false;
        }

        return $this->datetime >= $event->getConfiguration()->getCatalogOnlineDate();
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccessExternal(Event $event)
    {
        return $event->isExternalCatalogEnabled();
    }
}
