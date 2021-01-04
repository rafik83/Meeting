<?php

namespace Proximum\Vimeet\Application\Query\Event;

use Proximum\Vimeet\Domain\Model\Admin;

class EventListQuery
{
    const STATE_CURRENT = 'current';
    const STATE_PAST = 'past';
    const STATE_ARCHIVED = 'archived';

    /** @var Admin */
    public $admin;

    /** @var string */
    public $state;

    /**
     * @param Admin  $admin
     * @param string $state
     */
    public function __construct(Admin $admin, $state)
    {
        $this->admin = $admin;
        $this->state = $state;
    }
}
