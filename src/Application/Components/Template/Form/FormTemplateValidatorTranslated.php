<?php

namespace Proximum\Vimeet\Application\Components\Template\Form;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Template\Exception\NomenclatureMultipleMustBeOfDepthOneException;
use Proximum\Vimeet\Domain\Template\Exception\TemplateException;
use Proximum\Vimeet\Domain\Template\Exception\TemplateObjectMustHaveAtLeastOneSetterTagException;
use Proximum\Vimeet\Domain\Template\Form\FormTemplateValidator;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class FormTemplateValidatorTranslated
{
    /** @var FormTemplateValidator */
    private $templateValidator;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        FormTemplateValidator $templateValidator,
        TranslatorInterface $translator
    ) {
        $this->templateValidator = $templateValidator;
        $this->translator = $translator;
    }

    /**
     * @param TemplateData $templateData
     *
     * @throws TemplateException
     */
    public function validate(TemplateData $templateData): void
    {
        try {
            $this->templateValidator->validate($templateData);
        } catch (TemplateObjectMustHaveAtLeastOneSetterTagException $exception) {
            $objectsLabel = $this->getObjectsLabel($exception->templateObjects);

            throw new TemplateException(
                $this->translator->trans(
                    'template.registration.templateObjectMustHaveAtLeastOneSetterTag',
                    ['%objectsLabel%' => implode(', ', $objectsLabel)],
                    'templates'
                ),
                422
            );
        } catch (NomenclatureMultipleMustBeOfDepthOneException $exception) {
            $objectsLabel = $this->getObjectsLabel($exception->templateObjects);

            throw new TemplateException(
                $this->translator->trans(
                    'template.registration.nomenclatureCheckboxesMustBeOfDepthOne',
                    ['%objectsLabel%' => implode(', ', $objectsLabel)],
                    'templates'
                ),
                422
            );
        }
    }

    /**
     * @param TemplateObject[] $templateObjects
     *
     * @return string[]
     */
    private function getObjectsLabel(array $templateObjects): array
    {
        $objectsLabel = [];

        foreach ($templateObjects as $templateObject) {
            $objectsLabel[] = $templateObject->getDefaultLabel();
        }

        return $objectsLabel;
    }
}
