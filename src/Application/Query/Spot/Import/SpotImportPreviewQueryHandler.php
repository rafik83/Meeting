<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Spot\Import;

use Proximum\Vimeet\Application\Components\Spot\SpotImporter;
use Proximum\Vimeet\Domain\Spot\Import;

class SpotImportPreviewQueryHandler
{
    /** @var SpotImporter */
    private $spotImporter;

    /**
     * @param SpotImporter $spotImporter
     */
    public function __construct(SpotImporter $spotImporter)
    {
        $this->spotImporter = $spotImporter;
    }

    /**
     * @param SpotImportPreviewQuery $spotImportPreviewQuery
     *
     * @return Import[]
     */
    public function handle(SpotImportPreviewQuery $spotImportPreviewQuery): array
    {
        return $this->spotImporter->import(
            $spotImportPreviewQuery->event,
            $spotImportPreviewQuery->importedSpotFileName,
            $spotImportPreviewQuery->locale
        );
    }
}
