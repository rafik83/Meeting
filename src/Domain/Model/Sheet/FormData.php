<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class FormData
{
    /** @var Sheet */
    private $sheet;

    /** @var FormTemplate */
    private $formTemplate;

    /** @var array */
    private $data;

    public function __construct(Sheet $sheet, FormTemplate $formTemplate, array $data)
    {
        $this->sheet = $sheet;
        $this->formTemplate = $formTemplate;
        $this->data = $data;
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getFormTemplate(): FormTemplate
    {
        return $this->formTemplate;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function updateData(array $data): void
    {
        $this->data = $data;
    }
}
