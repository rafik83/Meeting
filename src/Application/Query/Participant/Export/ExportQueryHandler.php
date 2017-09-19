<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;

class ExportQueryHandler
{
    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /**
     * @param SerializerAdapterInterface  $serializerAdapter
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     */
    public function __construct(
        SerializerAdapterInterface $serializerAdapter,
        SheetSearchAdapterInterface $sheetSearchAdapter
    ) {
        $this->serializerAdapter  = $serializerAdapter;
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param ExportQuery $exportQuery
     *
     * @return string
     */
    public function handle(ExportQuery $exportQuery)
    {
        $participantsSheetIdsView = $this->sheetSearchAdapter->getParticipantsSheetIdsView(
            $exportQuery->event,
            $exportQuery->filters,
            $exportQuery->locale
        );

        return $this->serializerAdapter->serialize($participantsSheetIdsView, 'csv', [
            'locale'        => $exportQuery->locale,
            'charset'       => $exportQuery->charset,
            'event'         => $exportQuery->event,
            'csv_delimiter' => ';',
        ]);
    }
}
