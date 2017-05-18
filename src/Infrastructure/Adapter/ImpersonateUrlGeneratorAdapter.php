<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\ImpersonateUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;

class ImpersonateUrlGeneratorAdapter implements ImpersonateUrlGeneratorInterface
{
    /**
     * @var Impersonate
     */
    private $impersonate;

    /**
     * @var EventUrlGeneratorInterface
     */
    private $eventUrlGenerator;

    /**
     * ImpersonateUrlGeneratorAdapter constructor.
     *
     * @param Impersonate                $impersonate
     * @param EventUrlGeneratorInterface $eventUrlGenerator
     */
    public function __construct(
        Impersonate $impersonate,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->impersonate       = $impersonate;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Admin $admin, User $user, Event $event, $routeName, array $parameters = [])
    {
        $impersonationToken = $this->impersonate->getEncodedToken($admin, $user);

        return $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $event,
            $routeName,
            array_merge(['_switchto' => $impersonationToken], $parameters)
        );
    }
}
