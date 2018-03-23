<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Command;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCallHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\RawDataToParticipantConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;

class LeniApiCallHandlerTest extends TestCase
{
    public function testHandle()
    {
        $rawDataUser1 = [
            'Id' => 'user-id-1',
            'Email' => 'bruce@willis.usa',
            'Langue' => 'fr'
        ];

        $rawDataUser2 = [
            'Id' => 'user-id-2',
            'Email' => 'ronald@macdonald.food',
            'Langue' => 'en'
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

        $typeMappingEvent1 = ['type-mapping-event-1'];
        $typeMappingEvent2 = ['type-mapping-event-2'];

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

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventAndName($event1->reveal(), 'leni_user_id')
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $extraDataExists1 = $this->prophesize(ExtraData::class);
        $extraDataExists1->getValue()->shouldBeCalled()->willReturn('event-2-leni-user-id-1');
        $extraDataExists2 = $this->prophesize(ExtraData::class);
        $extraDataExists2->getValue()->shouldBeCalled()->willReturn('event-2-leni-user-id-2');
        $extraDataRepository
            ->getExtraDataForEventAndName($event2->reveal(), 'leni_user_id')
            ->shouldBeCalled()
            ->willReturn(
                [
                    $extraDataExists1->reveal(),
                    $extraDataExists2->reveal()
                ]
            )
        ;

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi
            ->get($event1->reveal(), ['field1', 'field2'], [], 0, 100)
            ->shouldBeCalled()
            ->willReturn([$rawDataUser1, $rawDataUser2])
        ;
        $leniApi
            ->get(
                $event2->reveal(),
                ['field1', 'field3'],
                [
                    [
                        'selectedFieldId' => 'Id',
                        'selectedOperator' => 'NOT_IN',
                        'value' => [
                            'event-2-leni-user-id-1',
                            'event-2-leni-user-id-2',
                        ],
                    ],
                ],
                0,
                100
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository
            ->findEventWithParameters([ExtraParameterType::TYPE_LENI_USER, ExtraParameterType::TYPE_LENI_EVENT])
            ->shouldBeCalled()
            ->willReturn([$event1->reveal(), $event2->reveal()])
        ;

        $rawDataToParticipantConverter = $this->prophesize(RawDataToParticipantConverter::class);
        $rawDataToParticipantConverter
            ->convert(
                $event1->reveal(),
                [$event1type1->reveal(), $event1type2->reveal()],
                ['type-mapping-event-1'],
                $rawDataUser1
            )
            ->shouldBeCalled()
        ;
        $rawDataToParticipantConverter
            ->convert(
                $event1->reveal(),
                [$event1type1->reveal(), $event1type2->reveal()],
                ['type-mapping-event-1'],
                $rawDataUser2
            )
            ->shouldBeCalled()
        ;

        $fieldsByEventQueryHandler = $this->prophesize(FieldsByEventQueryHandler::class);
        $fieldsByEventQueryHandler
            ->handle(new FieldsByEventQuery(['type-mapping-event-1']))
            ->shouldBeCalled()
            ->willReturn(['field1', 'field2'])
        ;
        $fieldsByEventQueryHandler
            ->handle(new FieldsByEventQuery(['type-mapping-event-2']))
            ->shouldBeCalled()
            ->willReturn(['field1', 'field3'])
        ;

        $leniApiCallHandler = new LeniApiCallHandler(
            $leniApi->reveal(),
            $eventRepository->reveal(),
            $typeRepository->reveal(),
            $extraDataRepository->reveal(),
            $mappingGetter->reveal(),
            $fieldsByEventQueryHandler->reveal(),
            $rawDataToParticipantConverter->reveal()
        );
        $leniApiCallHandler->handle(new LeniApiCall());
    }
}
