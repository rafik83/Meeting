<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\User\Event\ConfirmAuthenticationTokenImport;
use Proximum\Vimeet\Application\Command\User\Event\ConfirmAuthenticationTokenImportHandler;
use Proximum\Vimeet\Application\View\User\Event\AuthenticationTokenImportView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\AuthenticationToken;
use Proximum\Vimeet\Domain\Repository\User\Event\AuthenticationTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConfirmAuthenticationTokenImportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $now = new \DateTime('2020-03-24');
        $event = EventFactory::createEvent();
        $authenticationTokenRepository = $this->prophesize(AuthenticationTokenRepositoryInterface::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $user = $this->prophesize(User::class);

        $authenticationTokenImport1 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                '38016@example.net',
                'AABBCCDDEE',
                new \DateTime('2020-01-01')
            )
        );

        $authenticationTokenImport2 = new AuthenticationTokenImport(
            new AuthenticationTokenImportView(
                $event,
                'azerty',
                'FFGGHHIIKK'
            )
        );
        $authenticationTokenImport2->addError('validators.authentication_token.csv.email.error');

        $userRepository->findByEmail('38016@example.net')
            ->shouldBeCalled()
            ->willReturn($user->reveal());

        $authenticationTokenRepository->findByEventAndUser($event, $user->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $authenticationTokenRepository->add(
            new AuthenticationToken(
                $user->reveal(),
                $event,
                'AABBCCDDEE',
                $now,
                new \DateTime('2020-01-01')
            )
        )
        ->shouldBeCalled();

        $handler = new ConfirmAuthenticationTokenImportHandler(
            $authenticationTokenRepository->reveal(),
            $userRepository->reveal(),
            $now
        );
        $handler->handle(new ConfirmAuthenticationTokenImport([
            $authenticationTokenImport1,
            $authenticationTokenImport2
        ]));
    }
}
