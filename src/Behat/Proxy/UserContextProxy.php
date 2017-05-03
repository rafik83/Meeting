<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\UserContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\UserManager;

class UserContextProxy implements UserContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var UserManager */
    private $userManager;

    /**
     * @param StorageInterface $storage
     * @param UserManager      $userManager
     */
    public function __construct(StorageInterface $storage, UserManager $userManager)
    {
        $this->storage     = $storage;
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getuserManager()
    {
        return $this->userManager;
    }
}
