<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Registration;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Exception\Template\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\TemplateData;

class RegistrationTemplateValidator
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param TemplateData $templateData
     *
     * @throws RegistrationTemplateException
     */
    public function validate(TemplateData $templateData): void
    {
        $templateObjectMustHaveAtLeastOneSetterTag = [];

        foreach ($templateData->getEditableObjects() as $editableObject) {
            if (!$editableObject->hasAtLeastOneSetterTag()) {
                $templateObjectMustHaveAtLeastOneSetterTag[] = $editableObject->getDefaultLabel();
            }
        }

        if (!empty($templateObjectMustHaveAtLeastOneSetterTag)) {
            throw new RegistrationTemplateException(
                $this->translator->trans(
                    'template.registration.templateObjectMustHaveAtLeastOneSetterTag',
                    ['%objectsLabel%' => implode(', ', $templateObjectMustHaveAtLeastOneSetterTag)],
                    'templates'
                ),
                422
            );
        }
    }
}
