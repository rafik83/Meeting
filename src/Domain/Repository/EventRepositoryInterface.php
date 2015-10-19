<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventView;

interface EventRepositoryInterface
{
    /**
     * @param string $domain
     * @param string $locale
     *
     * @return EventView
     */
    public function getEventViewByDomain($domain, $locale);

    /**
     * @param integer $id
     *
     * @return Event
     */
    public function getById($id);
}
