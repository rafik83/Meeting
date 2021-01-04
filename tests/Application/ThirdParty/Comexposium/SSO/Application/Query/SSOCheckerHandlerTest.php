<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Query;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOChecker;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOCheckerHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSORegistrationTypeResolver;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\EmailToUserConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\ComboEmailUserNotValidException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\NoRegistrationTypeIsAvailableException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotOnEventException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\TokenChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class SSOCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $tokenChecker;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $emailToUserConverter;

    /** @var ObjectProphecy */
    private $SSORegistrationTypeResolver;

    /** @var ObjectProphecy */
    private $typeRepository;

    /** @var SSOCheckerHandler */
    private $handler;

    public function setUp()
    {
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->tokenChecker = $this->prophesize(TokenChecker::class);
        $this->emailToUserConverter = $this->prophesize(EmailToUserConverter::class);

        $this->SSORegistrationTypeResolver = $this->prophesize(SSORegistrationTypeResolver::class);
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $this->handler = new SSOCheckerHandler(
            $this->userRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->tokenChecker->reveal(),
            $this->emailToUserConverter->reveal(),
            $this->SSORegistrationTypeResolver->reveal(),
            $this->typeRepository->reveal()
        );
        $this->event = $this->prophesize(Event::class);
    }

    public function testNoUser()
    {
        $this->expectException(UserNotFoundException::class);
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn(null);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token', true, 'fr');
        $this->handler->handle($command);
    }

    public function testNoUserOnThisEvent()
    {
        $this->expectException(UserNotOnEventException::class);

        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('email@example.net');

        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())->willReturn([]);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token', true, 'fr');
        $this->handler->handle($command);
    }

    public function testComboEmailTokenNotValid()
    {
        $this->expectException(ComboEmailUserNotValidException::class);

        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('email@example.net');

        $sheet = $this->prophesize(Sheet::class);

        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $this->tokenChecker->isMailTokenComboValid('email@example.net', 'token')->shouldBeCalled()->willReturn(false);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token', true, 'fr');
        $this->handler->handle($command);
    }

    public function testHandle()
    {
        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('email@example.net');

        $sheet = $this->prophesize(Sheet::class);

        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $this->tokenChecker->isMailTokenComboValid('email@example.net', 'token')->shouldBeCalled()->willReturn(true);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token', true, 'fr');
        $result = $this->handler->handle($command);

        $this->assertEquals($user->reveal(), $result);
    }

    public function testHandleNotKnownVisitorLogin()
    {
        $this->event->getFallback()->willReturn('en');
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn(null);

        $this->tokenChecker->isMailTokenComboValid('email@example.net', 'token')->shouldBeCalled()->willReturn(true);

        $this->emailToUserConverter
            ->handle($this->event->reveal(), 'email@example.net', 'fr')
            ->shouldBeCalled()
            ->willReturn(new User('email@example.net', '', '', 'fr'))
        ;

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token', false, 'fr');
        $result = $this->handler->handle($command);

        $expectedUser = new User('email@example.net', '', '', 'fr');
        $this->assertEquals($expectedUser, $result);
    }

    public function testHandleKnownVisitorLogin()
    {
        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('email@example.net');

        $this->event->getFallback()->willReturn('en');
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->SSORegistrationTypeResolver->handle($this->event->reveal())->shouldBeCalled()->willReturn(null);
        $this->typeRepository->hasVisibleTypeByEvent($this->event->reveal())->shouldBeCalled()->willReturn(true);
        $this->tokenChecker->isMailTokenComboValid('email@example.net', 'token')->shouldBeCalled()->willReturn(true);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token', false, 'fr');
        $result = $this->handler->handle($command);
        $this->assertEquals($user->reveal(), $result);
    }

    public function testNoRegistrationTypeIsAvailableException()
    {
        $this->expectException(NoRegistrationTypeIsAvailableException::class);

        $user = $this->prophesize(User::class);
        $this->event->getFallback()->willReturn('en');
        $this->userRepository->findByEmail('email@example.net')->shouldBeCalled()->willReturn($user->reveal());
        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->SSORegistrationTypeResolver->handle($this->event->reveal())->shouldBeCalled()->willReturn(null);
        $this->typeRepository->hasVisibleTypeByEvent($this->event->reveal())->shouldBeCalled()->willReturn(false);

        $command = new SSOChecker($this->event->reveal(), 'email@example.net', 'token', false, 'fr');
        $result = $this->handler->handle($command);
    }
}
