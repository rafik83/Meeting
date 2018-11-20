<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

class UpdateParametersHandler
{
    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    public function __construct(FormTemplateRepositoryInterface $formTemplateRepository)
    {
        $this->formTemplateRepository = $formTemplateRepository;
    }

    public function handle(UpdateParameters $updateParameters): void
    {
        $formTemplate = $updateParameters->formTemplate;
        $formTemplate->update(
            $updateParameters->title,
            $updateParameters->translations,
            $updateParameters->published
        );

        $this->formTemplateRepository->update($formTemplate);
    }
}
