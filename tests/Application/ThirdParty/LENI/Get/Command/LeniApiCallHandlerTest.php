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
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command\LeniApiCallHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
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
        $leniApi->get($event1->reveal(), [], 0, 100)->shouldBeCalled();
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
        ;

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository
            ->findEventWithParameters([Type::TYPE_LENI_USER, Type::TYPE_LENI_EVENT])
            ->shouldBeCalled()
            ->willReturn([$event1->reveal(), $event2->reveal()])
        ;

        $leniApiCallHandler = new LeniApiCallHandler(
            $leniApi->reveal(),
            $eventRepository->reveal(),
            $extraDataRepository->reveal()
        );
        $leniApiCallHandler->handle(new LeniApiCall());
    }
}
