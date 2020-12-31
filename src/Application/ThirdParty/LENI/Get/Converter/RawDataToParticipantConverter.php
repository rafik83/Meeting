<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter;

use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Normalizer\LeniUserViewNormalizer;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\TemplateData\ParticipationTypeTemplateDataGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as UserEventExtraDataType;

class RawDataToParticipantConverter
{
    /** @var ConvertToParticipantHandler */
    private $convertToParticipantHandler;

    /** @var TypeConverter */
    private $typeConverter;

    /** @var DataConverter */
    private $dataConverter;

    /** @var ParticipationTypeTemplateDataGetter */
    private $participationTypeTemplateDataGetter;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var LeniUserViewQueryHandler */
    private $leniUserViewQueryHandler;

    /** @var LeniUserViewNormalizer */
    private $leniUserViewNormalizer;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ConvertToParticipantHandler $convertToParticipantHandler,
        TypeConverter $typeConverter,
        DataConverter $dataConverter,
        ParticipationTypeTemplateDataGetter $participationTypeTemplateDataGetter,
        ExtraDataRepositoryInterface $extraDataRepository,
        LeniUserViewQueryHandler $leniUserViewQueryHandler,
        LeniUserViewNormalizer $leniUserViewNormalizer,
        \DateTimeInterface $dateTime
    ) {
        $this->convertToParticipantHandler = $convertToParticipantHandler;
        $this->dateTime = $dateTime;
        $this->extraDataRepository = $extraDataRepository;
        $this->typeConverter = $typeConverter;
        $this->dataConverter = $dataConverter;
        $this->leniUserViewQueryHandler = $leniUserViewQueryHandler;
        $this->leniUserViewNormalizer = $leniUserViewNormalizer;
        $this->participationTypeTemplateDataGetter = $participationTypeTemplateDataGetter;
    }

    public function convert(
        Event $event,
        array $types,
        array $typesMapping,
        array $customDataMapping,
        array $rawUserData
    ): ?Participant {
        if (!isset($rawUserData[LeniConstants::LENI_COL_EMAIL]) || empty($rawUserData[LeniConstants::LENI_COL_EMAIL])) {
            return null;
        }

        $type = $this->typeConverter->convert($types, $typesMapping, $rawUserData);

        if (!$type instanceof Type) {
            return null;
        }

        $dataIndexedByTag = $this->dataConverter->convert($customDataMapping, $rawUserData);

        $participant = $this->convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $event,
                $type,
                $rawUserData[LeniConstants::LENI_COL_EMAIL],
                $rawUserData[LeniConstants::LENI_COL_LOCALE] ?? $event->getFallback(),
                $dataIndexedByTag,
                $this->participationTypeTemplateDataGetter->getRegistrationTemplateDataByType($type),
                $this->participationTypeTemplateDataGetter->getSheetTemplateDataByType($type),
                UserEventExtraDataType::LENI_USER_ID,
                $this->convertState($dataIndexedByTag)
            )
        );

        if ($participant instanceof Participant) {
            $this->addLeniUserIdInUserEventExtraData(
                $event,
                $participant->getUser(),
                $rawUserData[LeniConstants::LENI_COL_USER_ID]
            );

            $this->addLeniFingerprintInUserEventExtraData($event, $participant->getUser());
        }

        return $participant;
    }

    /**
     * @param array $dataIndexed
     *
     * @return string
     */
    private function convertState(array &$dataIndexed): string
    {
        if (!isset($dataIndexed[Sheet::SHEET_STATE])) {
            return Sheet::STATE_PENDING;
        }

        $rawState = $dataIndexed[Sheet::SHEET_STATE];
        $stateIndexedByLeniValue = array_flip(LeniConstants::SHEET_STATE_MAPPING);

        return $stateIndexedByLeniValue[$rawState] ?? Sheet::STATE_PENDING;
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $leniId
     */
    private function addLeniUserIdInUserEventExtraData(Event $event, User $user, string $leniId): void
    {
        $this->extraDataRepository->add(
            new ExtraData(
                $user,
                $event,
                UserEventExtraDataType::LENI_USER_ID,
                $leniId,
                $this->dateTime
            )
        );
    }

    /**
     * @param Event $event
     * @param User  $user
     */
    private function addLeniFingerprintInUserEventExtraData(Event $event, User $user): void
    {
        $leniUserView = $this->leniUserViewQueryHandler->handle(
            new LeniUserViewQuery($event, $user, null)
        );

        $leniUserData = $this->leniUserViewNormalizer->normalize($leniUserView);

        $this->extraDataRepository->add(
            new ExtraData(
                $user,
                $event,
                UserEventExtraDataType::LENI_FINGERPRINT,
                serialize($leniUserData),
                $this->dateTime
            )
        );
    }
}
