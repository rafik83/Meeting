<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;

interface LinkedSheetsRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return LinkedSheets[]
     */
    public function getByEvent(Event $event): array;
}
