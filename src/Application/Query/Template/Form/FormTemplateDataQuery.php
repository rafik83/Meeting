<?php

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class FormTemplateDataQuery implements Query
{
    /** @var FormTemplate */
    public $formTemplate;

    /** @var Sheet */
    public $sheet;

    /** @var Participant */
    public $participant;

    /** @var string */
    public $locale;

    public function __construct(
        FormTemplate $formTemplate,
        Sheet $sheet,
        Participant $participant,
        string $locale
    ) {
        $this->formTemplate = $formTemplate;
        $this->sheet = $sheet;
        $this->participant = $participant;
        $this->locale = $locale;
    }
}
