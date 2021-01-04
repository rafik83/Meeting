<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface ImpersonateUrlGeneratorInterface
{
    /**
     * @param Admin  $admin
     * @param User   $user
     * @param Event  $event
     * @param string $routeName
     * @param array  $parameters
     *
     * @return string
     */
    public function generate(Admin $admin, User $user, Event $event, $routeName, array $parameters = []);

    /**
     * @param string $routeName
     * @param array  $parameters
     *
     * @return string
     */
    public function generateExit($routeName, array $parameters = []);
}
