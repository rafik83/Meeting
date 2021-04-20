<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\SessionInterface as SessionAdapterInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionAdapter implements SessionAdapterInterface
{
    private SessionInterface $session;
    private FlashBagInterface $flashBag;

    public function __construct(SessionInterface $session, FlashBagInterface $flashBag)
    {
        $this->session = $session;
        $this->flashBag = $flashBag;
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
        return $this->flashBag->get($type, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function addToFlashBag($type, $message): void
    {
        $this->flashBag->add($type, $message);
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
}
