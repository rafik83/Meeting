<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionAdapter implements SessionInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * SessionAdapter constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->session->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getFromFlashBag($type, array $default = []): array
    {
        return $this->session->getFlashBag()->get($type, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function addToFlashBag($type, $message): void
    {
        $this->session->getFlashBag()->add($type, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $data): void
    {
        $this->session->set($key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->session->remove($key);
    }

    public function getLastUsed(): int
    {
        return $this->session->getMetadataBag()->getLastUsed();
    }
}
