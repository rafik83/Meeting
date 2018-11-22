<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\FormData;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

interface FormDataRepositoryInterface
{
    public function add(FormData $formData): void;
    public function update(FormData $formData): void;
    public function getBySheetAndFormTemplate(Sheet $sheet, FormTemplate $formTemplate): ?FormData;
}
