<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Registration;

use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException;
use Proximum\Vimeet\Domain\Template\TemplateData;

class RegistrationTemplateValidator
{
    /**
     * @param TemplateData $templateData
     *
     * @throws RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException
     */
    public function validate(TemplateData $templateData): void
    {
        $templateObjectWithNoSetterTag = [];

        foreach ($templateData->getEditableObjects() as $editableObject) {
            if (!$editableObject->hasAtLeastOneSetterTag()) {
                $templateObjectWithNoSetterTag[] = $editableObject;
            }
        }

        if (!empty($templateObjectWithNoSetterTag)) {
            throw new RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException($templateObjectWithNoSetterTag);
        }
    }
}
