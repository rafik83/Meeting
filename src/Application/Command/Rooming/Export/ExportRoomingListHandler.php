<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\RoomingListViewQuery;

class ExportRoomingListHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    public function __construct(
        QueryBusInterface $queryBus,
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->queryBus = $queryBus;
        $this->serializerAdapter = $serializerAdapter;
    }

    public function handle(ExportRoomingList $command): void
    {
        $this->queryBus->handle(new RoomingListViewQuery($command->event));
    }
}
