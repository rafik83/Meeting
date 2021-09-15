<?php

namespace Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;

class FormTemplateDataForUser implements Query
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var FormTemplate */
    public $formTemplate;

    /** @var string */
    public $locale;

    public function __construct(
        Event $event,
        User $user,
        FormTemplate $formTemplate,
        string $locale
    ) {
        $this->event = $event;
        $this->user = $user;
        $this->formTemplate = $formTemplate;
        $this->locale = $locale;
    }
}
