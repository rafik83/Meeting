<?php

namespace Proximum\Vimeet\Application\Query\Spot\Import;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class SpotImportPreviewQuery
{
    /** @var Event */
    public $event;

    /** @var File */
    public $importedSpotFileName;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param File   $importedSpotFileName
     * @param string $locale
     */
    public function __construct(Event $event, File $importedSpotFileName, string $locale)
    {
        $this->importedSpotFileName = $importedSpotFileName;
        $this->event = $event;
        $this->locale = $locale;
    }
}
