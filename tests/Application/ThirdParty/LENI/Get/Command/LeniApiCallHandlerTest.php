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
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCallHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class LeniApiCallHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();

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

        $user2 = $this->prophesize(User::class);
        $participantForUser2 = $this->prophesize(Participant::class);
        $participantForUser2->getUser()->shouldBeCalled()->willReturn($user2->reveal());

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

        $typeConverter = $this->prophesize(TypeConverter::class);
        $typeConverter
            ->convert([$event1type1->reveal(), $event1type2->reveal()], $typeMappingEvent1, $rawDataUser1)
            ->shouldBeCalled()
            ->willReturn($event1type2->reveal())
        ;
        $typeConverter
            ->convert([$event1type1->reveal(), $event1type2->reveal()], $typeMappingEvent1, $rawDataUser2)
            ->shouldBeCalled()
            ->willReturn($event1type1->reveal())
        ;

        $convertToParticipantHandler = $this->prophesize(ConvertToParticipantHandler::class);
        $convertToParticipantHandler
            ->handle(
                new ConvertToParticipant(
                    $event1->reveal(),
                    $event1type2->reveal(),
                    'bruce@willis.usa',
                    'fr',
                    [],
                    'leni_user_id'
                )
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $convertToParticipantHandler
            ->handle(
                new ConvertToParticipant(
                    $event1->reveal(),
                    $event1type1->reveal(),
                    'ronald@macdonald.food',
                    'en',
                    [],
                    'leni_user_id'
                )
            )
            ->shouldBeCalled()
            ->willReturn($participantForUser2->reveal())
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

        $extraDataRepository
            ->add(new ExtraData($user2->reveal(), $event1->reveal(), 'leni_user_id', 'user-id-2', $datetime))
            ->shouldBeCalled()
        ;

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi->get($event1->reveal(), [], 0, 100)->shouldBeCalled()->willReturn([$rawDataUser1, $rawDataUser2]);
        $leniApi
            ->get(
                $event2->reveal(),
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

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory->createRegistrationFromType($event1type1->reveal(), null)->shouldBeCalled();
        $templateDataFactory->createRegistrationFromType($event1type2->reveal(), null)->shouldBeCalled();
        $templateDataFactory->createSheetTemplateFromType($event1type1->reveal())->shouldBeCalled();
        $templateDataFactory->createSheetTemplateFromType($event1type2->reveal())->shouldBeCalled();

        $leniApiCallHandler = new LeniApiCallHandler(
            $leniApi->reveal(),
            $eventRepository->reveal(),
            $typeRepository->reveal(),
            $extraDataRepository->reveal(),
            $mappingGetter->reveal(),
            $typeConverter->reveal(),
            $convertToParticipantHandler->reveal(),
            $templateDataFactory->reveal(),
            $datetime
        );
        $leniApiCallHandler->handle(new LeniApiCall());
    }
}
