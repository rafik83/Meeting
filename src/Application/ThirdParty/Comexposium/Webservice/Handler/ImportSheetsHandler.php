<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
    private CONST STATUS_WHITE_LIST = ['VALIDE', 'INSTANCE'];

    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var RawRegistrationToRegistrationViewConverter */
    private $rawRegistrationToRegistrationViewConverter;

    /** @var ImportSheetHandler */
    private $importSheetHandler;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
     * @param ComexposiumWebservice                      $comexposiumWebservice
     * @param ExtraParameterRepositoryInterface          $extraParameterRepository
     * @param TypeRepositoryInterface                    $typeRepository
     * @param RawRegistrationToRegistrationViewConverter $rawRegistrationToRegistrationViewConverter
     * @param ImportSheetHandler                         $importSheetHandler
     * @param TemplateDataFactory                        $templateDataFactory
     */
    public function __construct(
        ComexposiumWebservice $comexposiumWebservice,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        TypeRepositoryInterface $typeRepository,
        RawRegistrationToRegistrationViewConverter $rawRegistrationToRegistrationViewConverter,
        ImportSheetHandler $importSheetHandler,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->comexposiumWebservice = $comexposiumWebservice;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->typeRepository = $typeRepository;
        $this->rawRegistrationToRegistrationViewConverter = $rawRegistrationToRegistrationViewConverter;
        $this->importSheetHandler = $importSheetHandler;
        $this->templateDataFactory = $templateDataFactory;
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
            ExtraParameterType::TYPE_COMEXPOSIUM_EVENT
        );

        $typeIdExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            ExtraParameterType::TYPE_COMEXPOSIUM_TYPE_ID
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

        $eventReference = $eventReferenceExtraParameter->getValue();

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType($type, null);

        $rawRegistrations = $this->comexposiumWebservice->getRegistrations($eventReference, $registrationReferences);

        foreach ($rawRegistrations as $rawRegistration) {
            $registrationView = $this->rawRegistrationToRegistrationViewConverter->convert($rawRegistration);

            if (!$this->isRegistrationHasAWhiteListedStatus($registrationView)) {
                continue;
            }

            $this->importSheetHandler->handle($event, $type, $registrationView, $registrationTemplate);
            $registrationTemplate->clear();
        }
    }

    /**
     * @param RegistrationView $registrationView
     *
     * @return bool
     */
    private function isRegistrationHasAWhiteListedStatus(RegistrationView $registrationView): bool
    {
        return \in_array($registrationView->status, self::STATUS_WHITE_LIST, true);
    }
}
