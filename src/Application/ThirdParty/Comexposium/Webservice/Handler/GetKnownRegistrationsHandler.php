<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\Exception\EventHasNotComexposiumReferenceException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Sheet\SheetExtraDataType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class GetKnownRegistrationsHandler
{
    private const CHUNK_SIZE = 100;

    /** @var ExtraDataRepositoryInterface */
    private $sheetExtraDataRepository;

    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /** @var GetEventReferenceHandler */
    private $getEventReferenceHandler;

    public function __construct(
        ComexposiumWebservice $comexposiumWebservice,
        ExtraDataRepositoryInterface $sheetExtraDataRepository,
        GetEventReferenceHandler $getEventReferenceHandler
    ) {
        $this->comexposiumWebservice = $comexposiumWebservice;
        $this->sheetExtraDataRepository = $sheetExtraDataRepository;
        $this->getEventReferenceHandler = $getEventReferenceHandler;
    }

    /**
     * @throws EventHasNotComexposiumReferenceException
     *
     * @return array of registration data indexed by Sheet id
     */
    public function handle(Event $event, int $chunkSize = self::CHUNK_SIZE): array
    {
        $eventReference = $this->getEventReferenceHandler->handle($event);

        $registrationReferencesExtraData = $this->sheetExtraDataRepository->getExtraDataByEventAndName(
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

        $rawRegistrationDataIndexedBySheetId = [];

        foreach (\array_chunk($registrationReferences, $chunkSize, false) as $referencesChunk) {
            foreach ($this->comexposiumWebservice->getRegistrations($eventReference, $referencesChunk) as $rawData) {
                if (!isset($rawData->reference, $sheetIdByReference[$rawData->reference])) {
                    continue;
                }

                $rawRegistrationDataIndexedBySheetId[$sheetIdByReference[$rawData->reference]] = $rawData;
            }
        }

        return $rawRegistrationDataIndexedBySheetId;
    }
}
