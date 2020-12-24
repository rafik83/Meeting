<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Command;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdate;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdateHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCallHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\RawDataToParticipantConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQueryHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class LeniApiCallHandlerTest extends TestCase
{
    public function testHandle()
    {
        $rawDataUser1 = [
            'Id' => 'user-id-1',
            'Email' => 'bruce@willis.usa',
            'Langue' => 'fr',
            'ModifieLe' => '/Date(1000000000000)/'
        ];

        $rawDataUser2 = [
            'Id' => 'user-id-2',
            'Email' => 'ronald@macdonald.food',
            'Langue' => 'en',
            'ModifieLe' => '/Date(2000000000000)/'
        ];

        $event1 = $this->prophesize(Event::class);
        $event2 = $this->prophesize(Event::class);

        $event1type1 = $this->prophesize(Type::class);
        $event1type1->getId()->willReturn(111);

        $event1type2 = $this->prophesize(Type::class);
        $event1type2->getId()->willReturn(222);

        $event2type1 = $this->prophesize(Type::class);
        $event2type1->getId()->willReturn(333);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository
            ->getTypesByEvent($event1->reveal())
            ->shouldBeCalled()
            ->willReturn([$event1type1->reveal(), $event1type2->reveal()])
        ;
        $typeRepository->getTypesByEvent($event2->reveal())->shouldBeCalled()->willReturn([$event2type1->reveal()]);

        $typeMappingEvent1 =[
            '23' => [
                'CategorieIndividuEvt' => 'Exhibitor',
                'ZL_PROFIL' => 'Whatever',
            ],
            '1337' => [
                'ZL_PROFIL' => 'Business',
                'CategorieIndividuEvt' => 'Visitor',
            ],
        ];
        $typeMappingEvent2 = ['type-mapping-event-2'];
        $customDataMappingEvent1 = ['custom-data-mapping-event-1'];
        $customDataMappingEvent2 = ['custom-data-mapping-event-2'];

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping($event1->reveal(), ExtraParameterType::TYPE_LENI_TYPES_MAPPING)
            ->shouldBeCalled()
            ->willReturn($typeMappingEvent1)
        ;
        $mappingGetter
            ->getMapping($event2->reveal(), ExtraParameterType::TYPE_LENI_TYPES_MAPPING)
            ->shouldBeCalled()
            ->willReturn($typeMappingEvent2)
        ;
        $mappingGetter
            ->getMapping($event1->reveal(), ExtraParameterType::TYPE_LENI_DATA_MAPPING)
            ->shouldBeCalled()
            ->willReturn($customDataMappingEvent1)
        ;
        $mappingGetter
            ->getMapping($event2->reveal(), ExtraParameterType::TYPE_LENI_DATA_MAPPING)
            ->shouldBeCalled()
            ->willReturn($customDataMappingEvent2)
        ;

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi
            ->get(
                $event1->reveal(),
                ['field1', 'field2', 'ModifieLe'],
                [
                    [
                        'selectedFieldId' => 'ModifieLe',
                        'selectedOperator' => 'GREATER_OR_EQUAL',
                        'value' => '/Date(1000000000000)/',
                    ],
                    [
                        'selectedFieldId' => 'CategorieIndividuEvt',
                        'selectedOperator' => 'IN',
                        'value' => [
                            'Exhibitor',
                            'Visitor',
                        ],
                    ],
                ],
                ['ModifieLe' => 'ASC'],
                0,
                100
            )
            ->shouldBeCalled()
            ->willReturn([$rawDataUser1, $rawDataUser2])
        ;
        $leniApi
            ->get($event2->reveal(), ['field1', 'field3', 'ModifieLe'], [], ['ModifieLe' => 'ASC'], 0, 100)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository
            ->findEventWithParameters([ExtraParameterType::TYPE_LENI_USER, ExtraParameterType::TYPE_LENI_EVENT])
            ->shouldBeCalled()
            ->willReturn([$event1->reveal()])
            ->willReturn([$event1->reveal(), $event2->reveal()])
        ;

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->willReturn(123);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->willReturn(963);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getSheet()->willReturn($sheet1->reveal());

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getSheet()->willReturn($sheet2->reveal());

        $rawDataToParticipantConverter = $this->prophesize(RawDataToParticipantConverter::class);
        $rawDataToParticipantConverter
            ->convert(
                $event1->reveal(),
                [$event1type1->reveal(), $event1type2->reveal()],
                $typeMappingEvent1,
                $customDataMappingEvent1,
                $rawDataUser1
            )
            ->shouldBeCalled()
            ->willReturn($participant1->reveal())
        ;
        $rawDataToParticipantConverter
            ->convert(
                $event1->reveal(),
                [$event1type1->reveal(), $event1type2->reveal()],
                $typeMappingEvent1,
                $customDataMappingEvent1,
                $rawDataUser2
            )
            ->shouldBeCalled()
            ->willReturn($participant2->reveal())
        ;

        $fieldsByEventQueryHandler = $this->prophesize(FieldsByEventQueryHandler::class);
        $fieldsByEventQueryHandler
            ->handle(new FieldsByEventQuery($typeMappingEvent1, $customDataMappingEvent1))
            ->shouldBeCalled()
            ->willReturn(['field1', 'field2', 'ModifieLe'])
        ;
        $fieldsByEventQueryHandler
            ->handle(new FieldsByEventQuery($typeMappingEvent2, $customDataMappingEvent2))
            ->shouldBeCalled()
            ->willReturn(['field1', 'field3', 'ModifieLe'])
        ;

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);

        $extraParameterEvent1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterEvent1->getValue()->willReturn('get');
        $extraParameterRepository
            ->findByEventAndType($event1->reveal(), ExtraParameterType::TYPE_LENI_MODE)
            ->shouldBeCalled()
            ->willReturn($extraParameterEvent1->reveal())
        ;

        $extraParameterEvent2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterEvent2->getValue()->willReturn('get');
        $extraParameterRepository
            ->findByEventAndType($event2->reveal(), ExtraParameterType::TYPE_LENI_MODE)
            ->shouldBeCalled()
            ->willReturn($extraParameterEvent2->reveal())
        ;

        $extraParameterEvent3 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterEvent3
            ->getValue()
            ->willReturn('[{"selectedFieldId": "CategorieIndividuEvt", "selectedOperator": "IN", "value": ["Exhibitor", "Visitor"]}]')
        ;
        $extraParameterRepository
            ->findByEventAndType($event1->reveal(), ExtraParameterType::TYPE_LENI_PREDEFINED_FILTERS)
            ->shouldBeCalled()
            ->willReturn($extraParameterEvent3->reveal())
        ;
        $extraParameterRepository
            ->findByEventAndType($event2->reveal(), ExtraParameterType::TYPE_LENI_PREDEFINED_FILTERS)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $eventExtraData = $this->prophesize(Event\ExtraData::class);
        $eventExtraData->getValue()->willReturn('/Date(1000000000000)/');
        $eventExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $eventExtraDataRepository
            ->getExtraDataForEvent(
                $event1->reveal(),
                'leni_get_last_updated_at'
            )
            ->shouldBeCalled()
            ->willReturn($eventExtraData->reveal())
        ;
        $eventExtraDataRepository
            ->getExtraDataForEvent(
                $event2->reveal(),
                'leni_get_last_updated_at'
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $addOrUpdateEventExtraDataHandler = $this->prophesize(AddOrUpdateHandler::class);
        $addOrUpdateEventExtraDataHandler
            ->handle(new AddOrUpdate($event1->reveal(), 'leni_get_last_updated_at', '/Date(2000000000000)/'))
            ->shouldBeCalled()
        ;

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->indexSheets([123, 963])->shouldBeCalled();

        $leniApiCallHandler = new LeniApiCallHandler(
            $leniApi->reveal(),
            $eventRepository->reveal(),
            $typeRepository->reveal(),
            $extraParameterRepository->reveal(),
            $mappingGetter->reveal(),
            $fieldsByEventQueryHandler->reveal(),
            $rawDataToParticipantConverter->reveal(),
            $eventExtraDataRepository->reveal(),
            $addOrUpdateEventExtraDataHandler->reveal(),
            $jobQueue->reveal()
        );
        $leniApiCallHandler->handle(new LeniApiCall());
    }
}
