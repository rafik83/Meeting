<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\VideoConference;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\VideoConference\SetVisioTested;
use Proximum\Vimeet\Application\Command\VideoConference\SetVisioTestedHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class SetVisioTestedHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $dateTime = new \DateTime;

        $userEventExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $userEventExtraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                Type::VISIO_TESTED,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $userEventExtraDataRepository
            ->add(new ExtraData($user->reveal(), $event->reveal(), Type::VISIO_TESTED, '1', $dateTime))
            ->shouldBeCalled()
        ;

        $setVisioTestedHandler = new SetVisioTestedHandler($userEventExtraDataRepository->reveal(), $dateTime);
        $setVisioTestedHandler->handle(new SetVisioTested($event->reveal(), $user->reveal()));
    }
}
