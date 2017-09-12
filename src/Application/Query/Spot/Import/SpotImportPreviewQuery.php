<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Spot\Import;

use Proximum\Vimeet\Domain\Model\File;

class SpotImportPreviewQuery
{
    /** @var File */
    public $importedSpotFileName;

    /**
     * @param File $importedSpotFileName
     */
    public function __construct(File $importedSpotFileName)
    {
        $this->importedSpotFileName = $importedSpotFileName;
    }
}
