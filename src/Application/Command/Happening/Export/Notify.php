<?php

namespace Proximum\Vimeet\Application\Command\Happening\Export;

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

    /** @var string */
    public $locale;

    /** @var File */
    public $file;

    public function __construct(Event $event, Admin $admin, string $locale, File $file)
    {
        $this->event = $event;
        $this->admin = $admin;
        $this->locale = $locale;
        $this->file = $file;
    }
}
