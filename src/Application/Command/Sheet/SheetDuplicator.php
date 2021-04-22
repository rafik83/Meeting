<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class SheetDuplicator implements Command
{
    /** @var Event */
    public $originEvent;

    /** @var Sheet[] */
    public $sheets;

    /** @var Admin */
    public $admin;

    /** @var Type */
    public $type;

    public function __construct(Event $originEvent, array $sheets, Admin $admin, Type $type)
    {
        $this->originEvent = $originEvent;
        $this->sheets = $sheets;
        $this->admin = $admin;
        $this->type = $type;
    }
}
