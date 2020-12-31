<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\TypeDoesNotMatchException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\CustomDataConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class LeniUserCustomDataQueryHandler
{
    /** @var TypeConverter */
    private $typeConverter;

    /** @var MappingGetter */
    private $mappingGetter;

    /** @var CustomDataConverter */
    private $customDataConverter;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    public function __construct(
        TypeConverter $typeConverter,
        MappingGetter $mappingGetter,
        CustomDataConverter $customDataConverter,
        TemplateDataFactory $templateDataFactory,
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->typeConverter = $typeConverter;
        $this->mappingGetter = $mappingGetter;
        $this->customDataConverter = $customDataConverter;
        $this->templateDataFactory = $templateDataFactory;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @throws TypeDoesNotMatchException
     */
    public function handle(LeniUserCustomDataQuery $leniUserCustomDataQuery): array
    {
        $data = $this->handleType($leniUserCustomDataQuery->event, $leniUserCustomDataQuery->type);

        $customData = $this->handleCustomData(
            $leniUserCustomDataQuery->event,
            $leniUserCustomDataQuery->sheet,
            $leniUserCustomDataQuery->user,
            $leniUserCustomDataQuery->locale
        );

        foreach ($customData as $fieldName => $value) {
            $data[$fieldName] = $value;
        }

        return $data;
    }

    /**
     * @throws TypeDoesNotMatchException
     */
    private function handleType(Event $event, Type $type): array
    {
        $typesMapping = $this->mappingGetter->getMapping(
            $event,
            EventExtraParameterType::TYPE_LENI_TYPES_MAPPING
        );

        if (null === $typesMapping) {
            return [];
        }

        return $this->typeConverter->convert($type, $typesMapping);
    }

    private function handleCustomData(Event $event, Sheet $sheet, User $user, string $locale): array
    {
        $customDataMapping = $this->mappingGetter->getMapping($event, EventExtraParameterType::TYPE_LENI_DATA_MAPPING);

        if (null === $customDataMapping) {
            return [];
        }

        $customData = [];

        $this->handleSheetState($sheet, $customData);
        $this->handleProductsData($event, $user, $customData);
        $this->getTaggedRawData($this->templateDataFactory->createFromSheet($sheet, $locale), $customData);
        $this->getTaggedRawData($this->templateDataFactory->createRegistrationFromSheet($sheet, $locale), $customData);

        $participant = $sheet->getUserParticipant($user);

        if (null !== $participant) {
            $this->getTaggedRawData(
                $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale),
                $customData
            );
        }

        return $this->customDataConverter->convert($customDataMapping, $customData);
    }

    private function handleSheetState(Sheet $sheet, array &$customData): void
    {
        $customData[LeniConstants::DATA_MAPPING_FORMAT_STATES][Sheet::SHEET_STATE] = LeniConstants::SHEET_STATE_MAPPING[
            $sheet->getState()
        ];
    }

    private function handleProductsData(Event $event, User $user, array &$customData): void
    {
        $productIds = array_merge(
            $this->productAttributedToParticipantRepository->findProductIdsAttributedByUserAndEvent(
                $user,
                $event
            ),
            $this->participantRepository->getProductIdsOfUserForEvent(
                $user,
                $event
            )
        );

        foreach ($productIds as $product) {
            $customData[LeniConstants::DATA_MAPPING_FORMAT_PRODUCTS][$product['id']] = true;
        }
    }

    private function getTaggedRawData(TemplateData $templateData, array &$customData): void
    {
        $typeTag = LeniConstants::DATA_MAPPING_FORMAT_TAGS;

        foreach ($templateData->getEditableObjects() as $object) {
            foreach ($object->getTags() as $tag) {
                if (!$object instanceof TemplateObject\ContentObjectInterface) {
                    continue;
                }

                if ($object instanceof TemplateObject\Nomenclature) {
                    if ($object->isMultiple()) {
                        $customData[$typeTag][$tag] = $object->getItems();
                    } else {
                        $customData[$typeTag][$tag] = $object->getItem();
                    }
                } else {
                    $customData[$typeTag][$tag] = $object->getContentValueLocalize();
                }
            }
        }
    }
}
