<?php

namespace Proximum\Vimeet\Domain\Template\Registration;

use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateNomenclatureCheckboxesMustBeOfDepthOneException;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException;
use Proximum\Vimeet\Domain\Template\Exception\UploadNotAllowedOnFirstStepOfRegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class RegistrationTemplateValidator
{
    /**
     * @param TemplateData $templateData
     *
     * @throws RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException
     * @throws RegistrationTemplateNomenclatureCheckboxesMustBeOfDepthOneException
     * @throws UploadNotAllowedOnFirstStepOfRegistrationTemplateException
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
                && $editableObject->isMultiple()
                && 1 !== $editableObject->getNomenclatureModel()->getDepth()
            ) {
                $nomenclatureWithWrongDepth[] = $editableObject;
            }
        }

        if ($templateData->getFirstBlock()->getUploadAndImageObjects()) {
            throw new UploadNotAllowedOnFirstStepOfRegistrationTemplateException(
                'Upload not allowed on first step of registration template'
            );
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
