<?php

namespace Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateData;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class MailToAdmin implements Command
{
    /** @var Admin */
    public $admin;

    /** @var File */
    public $file;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    public function __construct(
        Admin $admin,
        File $file,
        Event $event,
        string $locale
    ) {
        $this->admin = $admin;
        $this->file = $file;
        $this->event = $event;
        $this->locale = $locale;
    }
}
