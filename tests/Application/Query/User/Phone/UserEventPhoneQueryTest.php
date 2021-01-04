<?php

namespace Proximum\Vimeet\Tests\Application\Query\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\User\Phone\UserEventPhoneNotFoundException;
use Proximum\Vimeet\Application\Query\User\Phone\UserEventPhoneQuery;
use Proximum\Vimeet\Application\Query\User\Phone\UserEventPhoneQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class UserEventPhoneQueryTest extends TestCase
{
    public function testNotFound()
    {
        $this->expectException(UserEventPhoneNotFoundException::class);

        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(1);
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(1);

        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository
            ->find($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $query = new UserEventPhoneQuery($user->reveal(), $event->reveal());
        $handler = new UserEventPhoneQueryHandler($userEventPhoneRepository->reveal());
        $handler->handle($query);
    }

    public function testHandle()
    {
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $userEventPhone = $this->prophesize(User\UserEventPhone::class);
        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository
            ->find($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($userEventPhone)
        ;

        $query = new UserEventPhoneQuery($user->reveal(), $event->reveal());
        $handler = new UserEventPhoneQueryHandler($userEventPhoneRepository->reveal());
        $result = $handler->handle($query);

        $this->assertInstanceOf(User\UserEventPhone::class, $result);
        $this->assertEquals($userEventPhone->reveal(), $result);
    }
}
