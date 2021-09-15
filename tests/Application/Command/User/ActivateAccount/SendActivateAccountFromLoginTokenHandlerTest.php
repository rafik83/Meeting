<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\ActivateAccount;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\SendActivateAccountFromLoginToken;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\SendActivateAccountFromLoginTokenHandler;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ActivateAccountFromLoginEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SendActivateAccountFromLoginTokenHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $activateAccountTokenGenerator = $this->prophesize(ActivateAccountTokenGenerator::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $handler = new SendActivateAccountFromLoginTokenHandler(
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal()
        );

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $sheet->getEvent()->willReturn($event->reveal());

        $token = $this->prophesize(User\ActivateAccountToken::class);

        $activateAccountTokenGenerator
            ->generate($user->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($token);

        $eventDispatcher->dispatch(
            Events::USER_ACCOUNT_ACTIVATED_FROM_LOGIN,
            new ActivateAccountFromLoginEvent($user->reveal(), $event->reveal(), $token->reveal())
        )->shouldBeCalled();

        $handler->handle(new SendActivateAccountFromLoginToken($sheet->reveal(), $user->reveal()));
    }
}
