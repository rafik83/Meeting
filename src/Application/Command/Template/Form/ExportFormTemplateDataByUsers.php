<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class ExportFormTemplateDataByUsers implements Command
{
    /** @var Event */
    public $event;

    /** @var FormTemplate */
    public $formTemplate;

    /** @var int[] */
    public $users;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    public function __construct(
        Event $event,
        FormTemplate $formTemplate,
        array $users,
        Admin $admin,
        string $locale
    ) {
        $this->event = $event;
        $this->formTemplate = $formTemplate;
        $this->users = $users;
        $this->admin = $admin;
        $this->locale = $locale;
    }
}
