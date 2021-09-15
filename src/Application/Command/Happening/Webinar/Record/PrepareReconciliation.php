<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;

class PrepareReconciliation implements Command
{
    /** @var Happening */
    public $happening;

    /** @var \DateTimeInterface|null */
    public $dueDate;

    public function __construct(
        Happening $happening,
        ?\DateTimeInterface $dueDate
    ) {
        $this->happening = $happening;
        $this->dueDate = $dueDate;
    }
}
