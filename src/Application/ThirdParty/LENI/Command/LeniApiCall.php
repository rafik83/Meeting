<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Command;

use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

class LeniApiCall
{
    /** @var ExtraData */
    public $extraData;

    /**
     * @param ExtraData $extraData
     */
    public function __construct(ExtraData $extraData)
    {
        $this->extraData = $extraData;
    }
}
