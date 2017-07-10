<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Invoice;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\Invoice\PrefixManager;

interface PrefixContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * @return PrefixManager
     */
    public function getPrefixManager(): PrefixManager;
}
