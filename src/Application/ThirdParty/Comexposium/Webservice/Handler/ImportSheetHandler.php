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
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class ImportSheetHandler
{
    private CONST STATUS_WHITE_LIST = ['VALIDE', 'INSTANCE'];

    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var RawRegistrationToRegistrationViewConverter */
    private $rawRegistrationToRegistrationViewConverter;

    public function __construct(
        ComexposiumWebservice $comexposiumWebservice,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        RawRegistrationToRegistrationViewConverter $rawRegistrationToRegistrationViewConverter
    ) {
        $this->comexposiumWebservice = $comexposiumWebservice;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->rawRegistrationToRegistrationViewConverter = $rawRegistrationToRegistrationViewConverter;
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
            Type::TYPE_COMEXPOSIUM_EVENT
        );

        if (!$eventReferenceExtraParameter instanceof ExtraParameter) {
            return;
        }

        $eventReference = $eventReferenceExtraParameter->getValue();

        $rawRegistrations = $this->comexposiumWebservice->getRegistrations($eventReference, $registrationReferences);

        foreach ($rawRegistrations as $rawRegistration) {
            $registrationView = $this->rawRegistrationToRegistrationViewConverter->convert($rawRegistration);

            if (!$this->isRegistrationHasAWhiteListedStatus($registrationView)) {
                continue;
            }



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
