<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class BatchDuplicateSheets
{
    /** @var Event */
    public $originalEvent;

    /** @var Admin */
    public $admin;

    /** @var Type */
    public $type;

    /** @var int[] */
    public $ids;

    public function __construct(Event $originalEvent, Admin $admin, Type $type, array $ids)
    {
        $this->originalEvent = $originalEvent;
        $this->admin = $admin;
        $this->type = $type;
        $this->ids = $ids;
    }
}
