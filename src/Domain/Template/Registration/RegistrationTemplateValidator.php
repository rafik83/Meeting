<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Registration;

use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateNomenclatureCheckboxesMustBeOfDepthOneException;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class RegistrationTemplateValidator
{
    /**
     * @param TemplateData $templateData
     *
     * @throws RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException
     * @throws RegistrationTemplateNomenclatureCheckboxesMustBeOfDepthOneException
     */
    public function validate(TemplateData $templateData): void
    {
        $templateObjectWithNoSetterTag = [];
        $nomenclatureWithWrongDepth = [];

        foreach ($templateData->getEditableObjects() as $editableObject) {
            if (!$editableObject->hasAtLeastOneSetterTag()) {
                $templateObjectWithNoSetterTag[] = $editableObject;
            }

            if ($editableObject instanceof Nomenclature
                && $editableObject->isCheckboxes()
                && 1 !== $editableObject->getNomenclatureModel()->getDepth()
            ) {
                $nomenclatureWithWrongDepth[] = $editableObject;
            }
        }

        if (!empty($templateObjectWithNoSetterTag)) {
            throw new RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException($templateObjectWithNoSetterTag);
        }

        if (!empty($nomenclatureWithWrongDepth)) {
            throw new RegistrationTemplateNomenclatureCheckboxesMustBeOfDepthOneException(
                $nomenclatureWithWrongDepth
            );
        }
    }
}
