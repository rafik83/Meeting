<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\Query;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserCustomDataQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserCustomDataQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\TypeDoesNotMatchException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\CustomDataConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Country;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\Url;

class LeniUserCustomDataQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $type = $this->prophesize(Type::class);
        $participant = $this->prophesize(Participant::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getUserParticipant($user->reveal())->willReturn($participant->reveal());
        $sheet->getState()->willReturn('accepted');

        $typeMapping = ['whatever' => 'mapping'];
        $dataMapping = [
            'states' => ['sheet_state' => 'ZL_MODERATION'],
            'tags' => ['sheet_template_generic_tag_1' => 'leni_field_1'],
        ];

        $typeConverter = $this->prophesize(TypeConverter::class);
        $typeConverter
            ->convert($type->reveal(), $typeMapping)
            ->shouldBeCalled()
            ->willReturn(['type_leni_field' => 'value'])
        ;

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping($event->reveal(), EventExtraParameterType::TYPE_LENI_TYPES_MAPPING)
            ->shouldBeCalled()
            ->willReturn($typeMapping)
        ;
        $mappingGetter
            ->getMapping($event, EventExtraParameterType::TYPE_LENI_DATA_MAPPING)
            ->shouldBeCalled()
            ->willReturn($dataMapping)
        ;

        $customDataConverter = $this->prophesize(CustomDataConverter::class);
        $customDataConverter
            ->convert(
                $dataMapping,
                [
                    'states' => [
                        'sheet_state' => 'A'
                    ],
                    'tags' => [
                        'sheet_template_generic_tag_1' => 'A3',
                        'sheet_template_generic_tag_2' => ['B1', 'B2', 'B5'],
                        'participant_position' => 'Developper',
                        'sheet_country' => 'FR',
                        'sheet_website' => 'https://www.site.web'
                    ],
                    'products' => [
                        1 => true,
                        2 => true,
                    ]
                ]
            )
            ->shouldBeCalled()
            ->willReturn(['ZL_MODERATION' => 'A', 'leni_field_1' => ['A1', 'A3']])
        ;

        $sheetTemplateData = $this->prophesize(TemplateData::class);
        $registrationTemplateData = $this->prophesize(TemplateData::class);
        $participantTemplateData = $this->prophesize(TemplateData::class);
        $productAttributedToParticipantRepository = $this->prophesize(ProductAttributedToParticipantRepositoryInterface::class);

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory
            ->createFromSheet($sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData->reveal())
        ;
        $templateDataFactory
            ->createRegistrationFromSheet($sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData->reveal())
        ;
        $templateDataFactory
            ->createRegistrationFromParticipant($participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($participantTemplateData->reveal())
        ;

        $singleNomenclatureObject = new Nomenclature(
            'nomenclature-key',
            'nomenclature',
            [
                'tags' => ['sheet_template_generic_tag_1'],
                'mode' => 'singles',
            ],
            'fr',
            'fr'
        );
        $singleNomenclatureObject->setItems(['A3']);
        $checkboxesNomenclatureObject = new Nomenclature(
            'nomenclature-key',
            'nomenclature',
            [
                'tags' => ['sheet_template_generic_tag_2'],
                'mode' => 'checkboxes',
            ],
            'fr',
            'fr'
        );
        $checkboxesNomenclatureObject->setItems(['B1', 'B2', 'B5']);
        $sheetTemplateData->getEditableObjects()->shouldBeCalled()->willReturn(
            [
                $singleNomenclatureObject,
                $checkboxesNomenclatureObject,
            ]
        );

        $editableText = new EditableText(
            'editable-text-key',
            'editable-text',
            [
                'tags' => ['participant_position'],
            ],
            'fr',
            'fr'
        );
        $editableText->setContent('Developper');
        $participantTemplateData->getEditableObjects()->shouldBeCalled()->willReturn([$editableText]);

        $countryObject = new Country(
            'country-key',
            'country',
            [
                'tags' => ['sheet_country'],
            ],
            'fr',
            'fr'
        );
        $countryObject->setCountry('FR');

        $urlObject = new Url(
            'country-key',
            'country',
            [
                'tags' => ['sheet_website'],
            ],
            'fr',
            'fr'
        );
        $urlObject->setUrl('https://www.site.web');
        $registrationTemplateData->getEditableObjects()->shouldBeCalled()->willReturn([$countryObject, $urlObject]);

        $productAttributedToParticipantRepository
            ->findProductIdsAttributedByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([['id' => 1]])
        ;

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->getProductIdsOfUserForEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([['id' => 2]]);

        $leniUserCustomDataQueryHandler = new LeniUserCustomDataQueryHandler(
            $typeConverter->reveal(),
            $mappingGetter->reveal(),
            $customDataConverter->reveal(),
            $templateDataFactory->reveal(),
            $productAttributedToParticipantRepository->reveal(),
            $participantRepository->reveal()
        );

        $this->assertEquals(
            [
                'type_leni_field' => 'value',
                'leni_field_1' => ['A1', 'A3'],
                'ZL_MODERATION' => 'A',
            ],
            $leniUserCustomDataQueryHandler->handle(
                new LeniUserCustomDataQuery($event->reveal(), $user->reveal(), $type->reveal(), $sheet->reveal(), 'fr')
            )
        );
    }

    public function testTypeDoesNotMatchException()
    {
        $this->expectException(TypeDoesNotMatchException::class);

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $type = $this->prophesize(Type::class);

        $typeMapping = ['whatever' => 'mapping'];

        $typeConverter = $this->prophesize(TypeConverter::class);
        $typeConverter
            ->convert($type->reveal(), $typeMapping)
            ->shouldBeCalled()
            ->willThrow(TypeDoesNotMatchException::class)
        ;

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping($event->reveal(), EventExtraParameterType::TYPE_LENI_TYPES_MAPPING)
            ->shouldBeCalled()
            ->willReturn($typeMapping)
        ;

        $customDataConverter = $this->prophesize(CustomDataConverter::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $productAttributedToParticipantRepository = $this->prophesize(ProductAttributedToParticipantRepositoryInterface::class);

        $productAttributedToParticipantRepository
            ->findProductIdsAttributedByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldNotBeCalled()
        ;

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->getProductIdsOfUserForEvent($user->reveal(), $event->reveal())
            ->shouldNotBeCalled();

        $leniUserCustomDataQueryHandler = new LeniUserCustomDataQueryHandler(
            $typeConverter->reveal(),
            $mappingGetter->reveal(),
            $customDataConverter->reveal(),
            $templateDataFactory->reveal(),
            $productAttributedToParticipantRepository->reveal(),
            $participantRepository->reveal()
        );

        $leniUserCustomDataQueryHandler->handle(
            new LeniUserCustomDataQuery($event->reveal(), $user->reveal(), $type->reveal(), $sheet->reveal(), 'fr')
        );
    }
}
