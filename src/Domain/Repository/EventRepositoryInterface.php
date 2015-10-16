<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\EventView;

interface EventRepositoryInterface
{
    /**
     * @param string $domain
     *
     * @return EventView
     */
    public function getEventViewByDomain($domain);
}
