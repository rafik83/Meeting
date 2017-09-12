<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Spot\Import;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class SpotImportPreviewQuery
{
    /** @var Event */
    public $event;

    /** @var File */
    public $importedSpotFileName;

    /**
     * @param Event $event
     * @param File  $importedSpotFileName
     */
    public function __construct(Event $event, File $importedSpotFileName)
    {
        $this->importedSpotFileName = $importedSpotFileName;
        $this->event = $event;
    }
}
