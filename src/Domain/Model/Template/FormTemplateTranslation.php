<?php

namespace Proximum\Vimeet\Domain\Model\Template;

class FormTemplateTranslation
{
    /** @var int|null */
    private $id;

    /** @var FormTemplate */
    private $formTemplate;

    /** @var string */
    private $locale;

    /** @var string */
    private $title;

    public function __construct(FormTemplate $formTemplate, string $locale, string $title)
    {
        $this->formTemplate = $formTemplate;
        $this->locale = $locale;
        $this->title = $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormTemplate(): FormTemplate
    {
        return $this->formTemplate;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function update(string $title): void
    {
        $this->title = $title;
    }
}
