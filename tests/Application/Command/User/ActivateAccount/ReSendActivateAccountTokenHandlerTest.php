<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\ActivateAccount;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\ReSendActivateAccountToken;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\ReSendActivateAccountTokenHandler;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ReSendActivateAccountTokenHandlerTest extends TestCase
{
    public function testHandle()
    {
        $activateAccountTokenGenerator = $this->prophesize(ActivateAccountTokenGenerator::class);
        $eventDispatcher               = $this->prophesize(DelayedEventDispatcher::class);

        $handler = new ReSendActivateAccountTokenHandler(
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user  = $this->prophesize(User::class);
        $fromUser = UserFactory::create();
        $sheet->getEvent()->willReturn($event->reveal());

        $token = $this->prophesize(User\ActivateAccountToken::class);

        $activateAccountTokenGenerator
            ->generate($user->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($token)
        ;

        $eventDispatcher->dispatch(
            Events::USER_ACCOUNT_ACTIVATED,
            new ActivateAccountEvent($user->reveal(), $fromUser, $event->reveal(), $token->reveal(), $sheet->reveal())
        )->shouldBeCalled();

        $handler->handle(new ReSendActivateAccountToken($sheet->reveal(), $user->reveal(), $fromUser));
    }
}
