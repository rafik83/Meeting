<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class MailToAdmin implements Command
{
    /** @var Event */
    public $event;

    /** @var Admin */
    public $admin;

    /** @var File */
    public $file;

    /** @var string */
    public $locale;

    public function __construct(Event $event, Admin $admin, File $file, string $locale)
    {
        $this->event = $event;
        $this->admin = $admin;
        $this->file = $file;
        $this->locale = $locale;
    }
}
