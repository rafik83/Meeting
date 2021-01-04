<?php

namespace Proximum\Vimeet\Application\Components\Security;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\EventInterface;

class AdminEventAccess
{
    /**
     * @param Admin          $admin
     * @param EventInterface $event
     *
     * @return bool
     */
    public function canAccess(Admin $admin, EventInterface $event)
    {
        if (!$admin->hasEvents()) {
            return Admin::ROLE_SUPER_ADMIN === $admin->getRole();
        }

        return $admin->hasEvent($event);
    }
}
