<?php

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\FormData;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

interface FormDataRepositoryInterface
{
    public function add(FormData $formData): void;
    public function update(FormData $formData): void;
    public function save(FormData $formData): void;
    public function getBySheetAndFormTemplate(Sheet $sheet, FormTemplate $formTemplate): ?FormData;
}
