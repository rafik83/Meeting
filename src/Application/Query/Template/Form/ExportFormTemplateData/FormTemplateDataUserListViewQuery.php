<?php

namespace Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class FormTemplateDataUserListViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var FormTemplate */
    public $formTemplate;

    /** @var array */
    public $users;

    /** @var string */
    public $locale;

    public function __construct(Event $event, FormTemplate $formTemplate, array $users, string $locale)
    {
        $this->event = $event;
        $this->formTemplate = $formTemplate;
        $this->users = $users;
        $this->locale = $locale;
    }
}
