<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\UserEventView;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Domain\UserEventView\UserEventViewsFactory;

class UserEventViewsFactoryTest extends TestCase
{
    public function testGetByEvent()
    {
        $results = [
            [
                'sheetId' => 42,
                'ownerId' => 33,
                'ownerEmail' => 'michel@example.net',
                'ownerFirstName' => 'Michel',
                'ownerLastName' => 'BLANC',
                'ownerLocale' => 'fr',
                'userId' => 78,
                'userEmail' => 'chloe@example.net',
                'userFirstName' => 'Chloé',
                'userLastName' => 'HENRY',
                'userLocale' => 'en',
            ],
            [
                'sheetId' => 43,
                'ownerId' => 34,
                'ownerEmail' => 'julie@example.net',
                'ownerFirstName' => 'Julie',
                'ownerLastName' => 'DUPOND',
                'ownerLocale' => 'fr',
                'userId' => 33,
                'userEmail' => 'michel@example.net',
                'userFirstName' => 'Michel',
                'userLastName' => 'BLANC',
                'userLocale' => 'fr',
            ],
            [
                'sheetId' => 1456,
                'ownerId' => 99,
                'ownerEmail' => 'hello@example.net',
                'ownerFirstName' => null,
                'ownerLastName' => null,
                'ownerLocale' => 'en',
                'userId' => 99,
                'userEmail' => 'hello@example.net',
                'userFirstName' => null,
                'userLastName' => null,
                'userLocale' => 'en',
            ],
        ];

        $expectedUserEventViews = [
            33 => new UserEventView(
                777,
                33,
                'Michel',
                'BLANC',
                'michel@example.net',
                'fr',
                [
                    ['id' => 42],
                    ['id' => 43],
                ]
            ),
            78 => new UserEventView(
                777,
                78,
                'Chloé',
                'HENRY',
                'chloe@example.net',
                'en',
                [
                    ['id' => 42],
                ]
            ),
            34 => new UserEventView(
                777,
                34,
                'Julie',
                'DUPOND',
                'julie@example.net',
                'fr',
                [
                    ['id' => 43],
                ]
            ),
            99 => new UserEventView(
                777,
                99,
                null,
                null,
                'hello@example.net',
                'en',
                [
                    ['id' => 1456],
                ]
            ),
        ];

        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(777);

        $userEventViewRepository = $this->prophesize(UserEventViewRepositoryInterface::class);
        $userEventViewRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn($results);

        $userEventViewsFactory = new UserEventViewsFactory($userEventViewRepository->reveal());
        $userEventViews = $userEventViewsFactory->getByEvent($event->reveal());

        $this->assertEquals($expectedUserEventViews, $userEventViews);
    }
}
