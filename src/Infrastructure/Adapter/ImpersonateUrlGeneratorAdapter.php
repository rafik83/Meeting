<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImpersonateUrlGeneratorAdapter implements ImpersonateUrlGeneratorInterface
{
    /** @var Impersonate */
    private $impersonate;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(
        Impersonate $impersonate,
        EventUrlGeneratorInterface $eventUrlGenerator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->impersonate       = $impersonate;
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->urlGenerator      = $urlGenerator;
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

    /**
     * {@inheritdoc}
     */
    public function generateExit($routeName, array $parameters = [])
    {
        $redirectUrl = $this->urlGenerator->generate($routeName, $parameters);

        return $this->urlGenerator->generate('event', array_merge(
            [
                '_switchto' => '_exit',
                '_redirect' => $redirectUrl,
            ], $parameters
        ));
    }
}
