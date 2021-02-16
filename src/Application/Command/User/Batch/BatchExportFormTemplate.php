<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class BatchExportFormTemplate implements Command
{
    /** @var Event */
    public $event;

    /** @var null|FormTemplate */
    public $formTemplate;

    /** @var Admin */
    public $admin;

    /** @var int[] */
    public $ids;

    /** @var string */
    public $locale;

    public function __construct(
        Event $event,
        ?FormTemplate $formTemplate,
        Admin $admin,
        string $locale,
        array $ids
    ) {
        $this->event = $event;
        $this->formTemplate = $formTemplate;
        $this->admin = $admin;
        $this->ids = $ids;
        $this->locale = $locale;
    }
}
