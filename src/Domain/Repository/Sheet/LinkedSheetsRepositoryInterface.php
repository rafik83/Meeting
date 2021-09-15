<?php

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

    public function add(LinkedSheets $linkedSheets): void;

    public function remove(LinkedSheets $linkedSheets): void;
}
