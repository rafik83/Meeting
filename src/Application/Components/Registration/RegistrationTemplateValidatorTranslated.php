<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Registration;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException;
use Proximum\Vimeet\Domain\Template\Registration\RegistrationTemplateValidator;
use Proximum\Vimeet\Domain\Template\TemplateData;

class RegistrationTemplateValidatorTranslated
{
    /** @var RegistrationTemplateValidator */
    private $registrationTemplateValidator;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        RegistrationTemplateValidator $registrationTemplateValidator,
        TranslatorInterface $translator
    ) {
        $this->registrationTemplateValidator = $registrationTemplateValidator;
        $this->translator                    = $translator;
    }

    /**
     * @param TemplateData $templateData
     *
     * @throws RegistrationTemplateException
     */
    public function validate(TemplateData $templateData): void
    {
        try {
            $this->registrationTemplateValidator->validate($templateData);
        } catch (RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException $exception) {
            $objectsLabel = [];

            foreach ($exception->templateObjects as $templateObject) {
                $objectsLabel[] = $templateObject->getDefaultLabel();
            }

            throw new RegistrationTemplateException(
                $this->translator->trans(
                    'template.registration.templateObjectMustHaveAtLeastOneSetterTag',
                    ['%objectsLabel%' => implode(', ', $objectsLabel)],
                    'templates'
                ),
                422
            );
        }
    }
}
