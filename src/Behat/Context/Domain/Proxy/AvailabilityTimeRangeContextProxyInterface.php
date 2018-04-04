<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\AvailabilityTimeRangeManager;

interface AvailabilityTimeRangeContextProxyInterface
{
    public function getStorage(): StorageInterface;

    public function getAvailabilityTimeRangeManager(): AvailabilityTimeRangeManager;
}
