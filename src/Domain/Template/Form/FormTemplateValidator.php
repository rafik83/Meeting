<?php

namespace Proximum\Vimeet\Domain\Template\Form;

use Proximum\Vimeet\Domain\Template\Exception\NomenclatureMultipleMustBeOfDepthOneException;
use Proximum\Vimeet\Domain\Template\Exception\TemplateObjectMustHaveAtLeastOneSetterTagException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class FormTemplateValidator
{
    /**
     * @param TemplateData $templateData
     *
     * @throws TemplateObjectMustHaveAtLeastOneSetterTagException
     * @throws NomenclatureMultipleMustBeOfDepthOneException
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

        if (!empty($templateObjectWithNoSetterTag)) {
            throw new TemplateObjectMustHaveAtLeastOneSetterTagException($templateObjectWithNoSetterTag);
        }

        if (!empty($nomenclatureWithWrongDepth)) {
            throw new NomenclatureMultipleMustBeOfDepthOneException($nomenclatureWithWrongDepth);
        }
    }
}
