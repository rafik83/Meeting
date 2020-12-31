<?php

namespace Proximum\Vimeet\Domain\Repository\User\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;

interface VersionRepositoryInterface
{
    /**
     * @param Version $version
     */
    public function add(Version $version);

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return null|Version
     */
    public function getLastVersionByEventAndUser(Event $event, User $user): ?Version;

    /**
     * @param array $events
     */
    public function removeVersionsOfEvents(array $events): void;
}
