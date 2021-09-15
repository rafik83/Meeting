<?php

namespace Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData;

class UserListView
{
    /** @var UserDataView[] */
    public $userViews;

    /** @var string[] indexed by object key */
    public $formTemplateObjectLabels;

    /** @var string */
    public $locale;

    public function __construct(
        string $locale,
        array $userViews,
        array $formTemplateObjectLabels = []
    ) {
        $this->userViews = $userViews;
        $this->formTemplateObjectLabels = $formTemplateObjectLabels;
        $this->locale = $locale;
    }
}
