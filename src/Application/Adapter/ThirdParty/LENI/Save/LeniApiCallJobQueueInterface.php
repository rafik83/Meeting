<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ThirdParty\LENI\Save;

use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

interface LeniApiCallJobQueueInterface
{
    /**
     * @param ExtraData $extraData
     */
    public function createJob(ExtraData $extraData);
}
