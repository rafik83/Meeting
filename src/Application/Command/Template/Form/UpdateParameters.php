<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class UpdateParameters implements Command
{
    /** @var FormTemplate */
    public $formTemplate;

    /** @var string */
    public $title;

    /** @var array */
    public $translations;

    /** @var bool */
    public $published;

    public function __construct(FormTemplate $formTemplate)
    {
        $this->formTemplate = $formTemplate;
        $this->title = $formTemplate->getTitle();
        $this->published = $formTemplate->isPublished();

        foreach ($formTemplate->getEvent()->getLocales() as $eventLocale) {
            $this->translations[$eventLocale] = [
                'title' => $formTemplate->getLocalizedTitle($eventLocale),
            ];
        }
    }
}
