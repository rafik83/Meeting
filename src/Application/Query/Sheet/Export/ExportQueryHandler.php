<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Export;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;

class ExportQueryHandler
{
    /** @var SerializerAdapter */
    private $serializerAdapter;

    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /**
     * @param SerializerAdapter           $serializerAdapter
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     */
    public function __construct(SerializerAdapter $serializerAdapter, SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->serializerAdapter = $serializerAdapter;
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param ExportQuery $exportQuery
     *
     * @return string
     */
    public function handle(ExportQuery $exportQuery)
    {
        $sheetIdsView = $this->sheetSearchAdapter->getSheetIdsView(
            $exportQuery->event,
            $exportQuery->filters,
            $exportQuery->locale
        );

        return $this->serializerAdapter->serialize($sheetIdsView, 'csv', [
            'locale'  => $exportQuery->locale,
            'charset' => $exportQuery->charset,
            'event'   => $exportQuery->event,
        ]);
    }
}
