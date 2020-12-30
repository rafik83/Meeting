<?php

namespace Proximum\Vimeet\Application\View\Type;

use Proximum\Vimeet\Application\View\FormTemplate\FormTemplateView;

class TypeListView
{
    /** @var int */
    public $id;

    /** @var null|int */
    public $position;

    /** @var string */
    public $title;

    /** @var bool */
    public $hidden;

    /** @var string */
    public $registrationTemplate;

    /** @var string */
    public $sheetTemplate;

    /** @var FormTemplateView[] */
    public $formTemplateViews;

    /** @var string */
    public $package;

    /** @var bool */
    public $hasSpecificPaymentConditions;

    /** @var bool */
    public $hasSpecificTermsOfSale;

    /** @var bool */
    public $canRemoveMeeting;

    /** @var bool */
    public $canMoveMeeting;

    public function __construct(
        int $id,
        ?int $position,
        string $title,
        bool $hidden,
        string $registrationTemplate,
        string $sheetTemplate,
        array $formTemplateViews,
        string $package,
        bool $hasSpecificPaymentConditions = false,
        bool $hasSpecificTermsOfSale = false,
        bool $canMoveMeeting = false,
        bool $canRemoveMeeting = false
    ) {
        $this->id = $id;
        $this->position = $position;
        $this->title = $title;
        $this->hidden = $hidden;
        $this->registrationTemplate = $registrationTemplate;
        $this->sheetTemplate = $sheetTemplate;
        $this->formTemplateViews = $formTemplateViews;
        $this->package = $package;
        $this->hasSpecificPaymentConditions = $hasSpecificPaymentConditions;
        $this->hasSpecificTermsOfSale = $hasSpecificTermsOfSale;
        $this->canRemoveMeeting = $canRemoveMeeting;
        $this->canMoveMeeting = $canMoveMeeting;
    }
}
