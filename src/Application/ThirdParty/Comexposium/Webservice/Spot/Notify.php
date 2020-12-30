<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class Notify implements Command
{
    /** @var Admin */
    public $admin;

    /** @var Event */
    public $event;

    /** @var File */
    public $file;

    public function __construct(Event $event, Admin $admin, File $file)
    {
        $this->event = $event;
        $this->admin = $admin;
        $this->file = $file;
    }
}
