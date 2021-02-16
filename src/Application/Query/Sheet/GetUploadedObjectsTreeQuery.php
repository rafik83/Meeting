<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class GetUploadedObjectsTreeQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Sheet[] */
    public $sheets;

    /** @var Admin */
    public $admin;

    public function __construct(Event $event, array $sheets, Admin $admin)
    {
        $this->event = $event;
        $this->sheets = $sheets;
        $this->admin = $admin;
    }
}
