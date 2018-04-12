<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Sheet\SheetExtraDataType;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class GetKnownRegistrationsHandler
{
    private const CHUNK_SIZE = 100;

    /** @var ExtraDataRepositoryInterface */
    private $sheetExtraDataRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    public function __construct(
        ExtraDataRepositoryInterface $sheetExtraDataRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ComexposiumWebservice $comexposiumWebservice
    ) {
        $this->sheetExtraDataRepository = $sheetExtraDataRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->comexposiumWebservice = $comexposiumWebservice;
    }

    public function handle(Event $event, int $chunkSize = self::CHUNK_SIZE): array
    {
        $eventReferenceExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            ExtraParameterType::TYPE_COMEXPOSIUM_EVENT_REFERENCE
        );

        if (!$eventReferenceExtraParameter instanceof ExtraParameter) {
            throw new \LogicException(
                'Not allowed to call this GetSpotsHandler if event has not Comexposium event reference'
            );
        }

        $registrationReferencesExtraData = $this->sheetExtraDataRepository->getExtraDataByNameAndEvent(
            $event,
            SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE
        );

        if (empty($registrationReferencesExtraData)) {
            return [];
        }

        $sheetIdByReference = [];
        $registrationReferences = [];

        foreach ($registrationReferencesExtraData as $extraData) {
            $sheetIdByReference[$extraData->getValue()] = $extraData->getSheet()->getId();
            $registrationReferences[] = $extraData->getValue();
        }

        $rawRegistrations = [];

        foreach (\array_chunk($registrationReferences, $chunkSize, false) as $registrationReferencesChunk) {
            foreach ($this->comexposiumWebservice->getRegistrations(
                $eventReferenceExtraParameter->getValue(),
                $registrationReferencesChunk
            ) as $rawData) {
                $rawRegistrations[] = $rawData;
            }
        }

        return $rawRegistrations;
    }
}
