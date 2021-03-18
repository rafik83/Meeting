<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter\RawRegistrationToRegistrationViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class ImportSheetsHandler
{
    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var RawRegistrationToRegistrationViewConverter */
    private $rawRegistrationToRegistrationViewConverter;

    /** @var ConvertRegistrationViewToSheet */
    private $convertRegistrationViewToSheet;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var RemoveAlreadyImportedReferences */
    private $removeAlreadyImportedReferences;

    /** @var PrepareNomenclatureHandler */
    private $prepareNomenclatureHandler;

    /**
     * @param ComexposiumWebservice                      $comexposiumWebservice
     * @param ExtraParameterRepositoryInterface          $extraParameterRepository
     * @param TypeRepositoryInterface                    $typeRepository
     * @param RawRegistrationToRegistrationViewConverter $rawRegistrationToRegistrationViewConverter
     * @param ConvertRegistrationViewToSheet             $convertRegistrationViewToSheet
     * @param TemplateDataFactory                        $templateDataFactory
     * @param RemoveAlreadyImportedReferences            $removeAlreadyImportedReferences
     * @param PrepareNomenclatureHandler                 $prepareNomenclatureHandler
     */
    public function __construct(
        ComexposiumWebservice $comexposiumWebservice,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        TypeRepositoryInterface $typeRepository,
        RawRegistrationToRegistrationViewConverter $rawRegistrationToRegistrationViewConverter,
        ConvertRegistrationViewToSheet $convertRegistrationViewToSheet,
        TemplateDataFactory $templateDataFactory,
        RemoveAlreadyImportedReferences $removeAlreadyImportedReferences,
        PrepareNomenclatureHandler $prepareNomenclatureHandler
    ) {
        $this->comexposiumWebservice = $comexposiumWebservice;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->typeRepository = $typeRepository;
        $this->rawRegistrationToRegistrationViewConverter = $rawRegistrationToRegistrationViewConverter;
        $this->convertRegistrationViewToSheet = $convertRegistrationViewToSheet;
        $this->templateDataFactory = $templateDataFactory;
        $this->removeAlreadyImportedReferences = $removeAlreadyImportedReferences;
        $this->prepareNomenclatureHandler = $prepareNomenclatureHandler;
    }

    /**
     * @param Event $event
     * @param array $registrationReferences
     *
     * @throws \SoapFault
     */
    public function handle(Event $event, array $registrationReferences): void
    {
        $eventReferenceExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            ExtraParameterType::TYPE_COMEXPOSIUM_EVENT_REFERENCE
        );

        $typeIdExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            ExtraParameterType::TYPE_COMEXPOSIUM_EXHIBITOR_TYPE_ID
        );

        if (!$eventReferenceExtraParameter instanceof ExtraParameter
            || !$typeIdExtraParameter instanceof ExtraParameter
        ) {
            return;
        }

        $type = $this->typeRepository->getById((int) $typeIdExtraParameter->getValue());

        if (!$type instanceof Type || $type->getEvent()->getId() !== $event->getId()) {
            return;
        }

        $registrationReferences = $this->removeAlreadyImportedReferences->handle($event, $registrationReferences);

        if (empty($registrationReferences)) {
            return;
        }

        $eventReference = $eventReferenceExtraParameter->getValue();

        $nomenclatureView = $this->prepareNomenclatureHandler->handle($eventReference);

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType($type, null);
        $sheetTemplate = $this->templateDataFactory->createSheetTemplateFromType($type);

        $rawRegistrations = $this->comexposiumWebservice->getRegistrations($eventReference, $registrationReferences);

        foreach ($rawRegistrations as $rawRegistration) {
            $registrationView = $this->rawRegistrationToRegistrationViewConverter->convert(
                $rawRegistration,
                $nomenclatureView
            );

            if (!$registrationView instanceof RegistrationView) {
                continue;
            }

            $this->convertRegistrationViewToSheet->handle(
                $event,
                $type,
                $registrationView,
                $registrationTemplate,
                $sheetTemplate
            );

            // clear data in templates to be reused
            $registrationTemplate->clear();
            $sheetTemplate->clear();
        }
    }
}
