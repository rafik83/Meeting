<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;

interface EventRepositoryInterface
{
    /**
     * @param string $domain
     *
     * @return Event
     */
    public function findByDomain($domain);
}
