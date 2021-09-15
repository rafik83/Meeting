<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Download;

use DateTime;
use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;

class PlanDownloadRecordArchive implements Command
{
    /** @var Happening */
    public $happening;

    /** @var DateTime */
    public $dueDate;

    public function __construct(
        Happening $happening,
        DateTime $dueDate
    ) {
        $this->happening = $happening;
        $this->dueDate = $dueDate;
    }
}
