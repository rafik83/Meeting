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
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\ConvertToSheet;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;

class LeniApiCallHandlerTest extends TestCase
{
    public function testHandle()
    {
        $datetime = new \DateTime();
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $event1 = $this->prophesize(Event::class);
        $event2 = $this->prophesize(Event::class);
        $event1type = $this->prophesize(Type::class);
        $event2type = $this->prophesize(Type::class);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getTypesByEvent($event1->reveal())->shouldBeCalled()->willReturn([$event1type->reveal()]);
        $typeRepository->getTypesByEvent($event2->reveal())->shouldBeCalled()->willReturn([$event2type->reveal()]);

        $typeMappingEvent2 = ['type-mapping-event-2'];

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping($event1->reveal(), ExtraParameterType::TYPE_LENI_TYPES_MAPPING)
            ->shouldBeCalled()
            ->willReturn(['type-mapping-event-1'])
        ;
        $mappingGetter
            ->getMapping($event2->reveal(), ExtraParameterType::TYPE_LENI_TYPES_MAPPING)
            ->shouldBeCalled()
            ->willReturn($typeMappingEvent2)
        ;

        $rawUser1 = ['raw-user-1'];
        $rawUser2 = ['raw-user-2'];
        $convertToSheet = $this->prophesize(ConvertToSheet::class);
        $convertToSheet->handle($event1->reveal(), [$event2type->reveal()], $typeMappingEvent2, $rawUser1)->shouldBeCalled();
        $convertToSheet->handle($event1->reveal(), [$event2type->reveal()], $typeMappingEvent2, $rawUser2)->shouldBeCalled();

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventAndName($event1->reveal(), 'leni_user_id')
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $extraDataRepository
            ->getExtraDataForEventAndName($event2->reveal(), 'leni_user_id')
            ->shouldBeCalled()
            ->willReturn(
                [
                    new ExtraData($user1->reveal(), $event2->reveal(), 'leni_user_id', 'leni-user-id-1', $datetime),
                    new ExtraData($user2->reveal(), $event2->reveal(), 'leni_user_id', 'leni-user-id-2', $datetime),
                ]
            )
        ;

        $leniApi = $this->prophesize(LeniApiCaller::class);
        $leniApi->get($event1->reveal(), [], 0, 100)->shouldBeCalled()->willReturn([]);
        $leniApi
            ->get(
                $event2->reveal(),
                [
                    [
                        'selectedFieldId' => 'Id',
                        'selectedOperator' => 'NOT_IN',
                        'value' => [
                            'leni-user-id-1',
                            'leni-user-id-2',
                        ],
                    ],
                ],
                0,
                100
            )
            ->shouldBeCalled()
            ->willReturn([$rawUser1, $rawUser2])
        ;

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository
            ->findEventWithParameters([ExtraParameterType::TYPE_LENI_USER, ExtraParameterType::TYPE_LENI_EVENT])
            ->shouldBeCalled()
            ->willReturn([$event1->reveal(), $event2->reveal()])
        ;

        $leniApiCallHandler = new LeniApiCallHandler(
            $leniApi->reveal(),
            $eventRepository->reveal(),
            $typeRepository->reveal(),
            $extraDataRepository->reveal(),
            $mappingGetter->reveal(),
            $convertToSheet->reveal()
        );
        $leniApiCallHandler->handle(new LeniApiCall());
    }
}
