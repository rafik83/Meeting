<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ThirdParty\Vianeo;

use Proximum\Vimeet\Domain\Model\Sheet;

interface VianeoApiCallJobQueueInterface
{
    /**
     * @param Sheet $sheet
     */
    public function createJob(Sheet $sheet);
}
