<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

/**
 * User state changed or there was no previous one, and new state is "validated"
 */
class HasUserSheetStateChangedQueryHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var MappingGetter */
    private $mappingGetter;

    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        MappingGetter $mappingGetter
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->mappingGetter = $mappingGetter;
    }

    public function handle(HasUserSheetStateChangedQuery $query): bool
    {
        $customDataMapping = $this->mappingGetter->getMapping(
            $query->event,
            EventExtraParameterType::TYPE_LENI_DATA_MAPPING
        );

        if (!isset($customDataMapping[LeniConstants::DATA_MAPPING_FORMAT_STATES][Sheet::SHEET_STATE])) {
            return false;
        }

        $previousState = null;
        $stateLeniFieldName = $customDataMapping[LeniConstants::DATA_MAPPING_FORMAT_STATES][Sheet::SHEET_STATE];
        $currentState = $query->data[$stateLeniFieldName] ?? null;

        $previousLeniFingerprintExtraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            Type::LENI_FINGERPRINT,
            $query->user
        );

        if ($previousLeniFingerprintExtraData instanceof User\Event\ExtraData) {
            $previousData = unserialize($previousLeniFingerprintExtraData->getValue(), ['allowed_classes' => false]);
            $previousState = $previousData[$stateLeniFieldName] ?? null;
        }

        $isUserSheetStateValidated = LeniConstants::SHEET_STATE_MAPPING[Sheet::STATE_VALIDATED] === $currentState;
        $hasStateChanged = $previousState !== $currentState;

        return $hasStateChanged && $isUserSheetStateValidated;
    }
}
