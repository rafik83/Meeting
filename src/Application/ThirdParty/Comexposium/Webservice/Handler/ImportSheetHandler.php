<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class ImportSheetHandler
{
    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(
        ComexposiumWebservice $comexposiumWebservice,
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->comexposiumWebservice = $comexposiumWebservice;
        $this->extraParameterRepository = $extraParameterRepository;
    }

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

        $registration = $this->comexposiumWebservice->getRegistrations($eventReference, $registrationReferences);
        dump($registration);
    }
}
