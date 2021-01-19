<?php

namespace Proximum\Vimeet\Application\Components\Registration;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateNomenclatureCheckboxesMustBeOfDepthOneException;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException;
use Proximum\Vimeet\Domain\Template\Exception\UploadNotAllowedOnFirstStepOfRegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\Registration\RegistrationTemplateValidator;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;

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
            $objectsLabel = $this->getObjectsLabel($exception->templateObjects);

            throw new RegistrationTemplateException(
                $this->translator->trans(
                    'template.registration.templateObjectMustHaveAtLeastOneSetterTag',
                    ['%objectsLabel%' => implode(', ', $objectsLabel)],
                    'templates'
                ),
                422
            );
        } catch (RegistrationTemplateNomenclatureCheckboxesMustBeOfDepthOneException $exception) {
            $objectsLabel = $this->getObjectsLabel($exception->templateObjects);

            throw new RegistrationTemplateException(
                $this->translator->trans(
                    'template.registration.nomenclatureCheckboxesMustBeOfDepthOne',
                    ['%objectsLabel%' => implode(', ', $objectsLabel)],
                    'templates'
                ),
                422
            );
        } catch (UploadNotAllowedOnFirstStepOfRegistrationTemplateException $exception) {
            throw new RegistrationTemplateException(
                $this->translator->trans(
                    'template.registration.uploadNotAllowedOnFirstStepOfRegistrationTemplate',
                    [],
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
