<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\RuleManager;

interface RuleContextProxyInterface
{
    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;

    /**
     * @return RuleManager
     */
    public function getRuleManager(): RuleManager;
}
