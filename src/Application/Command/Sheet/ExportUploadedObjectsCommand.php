<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class ExportUploadedObjectsCommand implements Command
{
    /** @var Sheet[] */
    public $sheets;

    /** @var Admin */
    public $admin;

    /** @var Event */
    public $event;

    public function __construct(array $sheets, Admin $admin, Event $event)
    {
        $this->sheets = $sheets;
        $this->admin = $admin;
        $this->event = $event;
    }
}
