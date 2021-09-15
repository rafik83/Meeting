<?php

namespace Proximum\Vimeet\Tests\Application\Command\User;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\User\ChangeMailActivation;
use Proximum\Vimeet\Application\Command\User\ChangeMailActivationHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\UserEmailChangeActivatedEvent;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ChangeMailActivationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // Base
        $date = new DateTime();
        $user = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');

        // Actual
        $changeMailToken = new ChangeMailToken($user, 'toto@toto.fr', '1234567890', $date);
        $changeMailActivation = new ChangeMailActivation($changeMailToken);

        // Expected
        $expectedUser = new User('toto@toto.fr', '__SALT__', '__TEST__', 'fr');

        // Mock
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->set($expectedUser)->shouldBeCalled();

        $changeMailTokenRepository = $this->prophesize(ChangeMailTokenRepositoryInterface::class);
        $changeMailTokenRepository->deleteAllForUser($user)->shouldBeCalled();

        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $delayedEventDispatcher
            ->dispatch(Events::USER_EMAIL_CHANGE_ACTIVATED, new UserEmailChangeActivatedEvent($expectedUser))
            ->shouldBeCalled()
        ;

        // Handler
        $handler = new ChangeMailActivationHandler(
            $userRepository->reveal(),
            $changeMailTokenRepository->reveal(),
            $delayedEventDispatcher->reveal()
        );
        $handler->handle($changeMailActivation);
    }
}
