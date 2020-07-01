<?php

namespace Proximum\Vimeet\Tests\Application\Command\User;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Command\User\ChangeMail;
use Proximum\Vimeet\Application\Command\User\ChangeMailHandler;
use Proximum\Vimeet\Application\Components\Token\ChangeMailTokenGenerator;
use Proximum\Vimeet\Application\Event\User\ChangeMailAddressEvent;
use Proximum\Vimeet\Application\Exception\Field\EmptyFieldException;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Application\Exception\User\InvalidPasswordException;
use Proximum\Vimeet\Application\Exception\User\SameEmailException;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChangeMailHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $tokenGenerator;

    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $changeMailTokenRepository;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $passwordEncoder;

    /** @var \DateTime */
    private $date;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->tokenGenerator = $this->prophesize(ChangeMailTokenGenerator::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->changeMailTokenRepository = $this->prophesize(ChangeMailTokenRepositoryInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $this->date = new DateTime();
        $this->user = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');
        $this->event = EventFactory::createEvent();
    }

    public function testHandle()
    {
        // Expected
        $expectedChangeMailToken = new ChangeMailToken($this->user, 'toto@toto.fr', '1234567890', $this->date);
        $expectedEvent = new ChangeMailAddressEvent($this->user, $this->event, $expectedChangeMailToken);

        // Mock
        $this->tokenGenerator->generate($this->user, 'toto@toto.fr')->shouldBeCalled()->willReturn($expectedChangeMailToken);

        $this->userRepository->findByEmail('toto@toto.fr')->shouldBeCalled()->willReturn(null);

        $this->changeMailTokenRepository->deleteAllForUser($this->user)->shouldBeCalled();
        $this->changeMailTokenRepository->create($expectedChangeMailToken)->shouldBeCalled();

        $this->eventDispatcher->dispatch('change_mail', $expectedEvent)->shouldBeCalled();

        $this->passwordEncoder->encode($this->user, 'plain_password')->shouldBeCalled()->willReturn('__TEST__');

        // Base
        $changeMail = new ChangeMail($this->user, $this->event);
        $changeMail->mail = 'toto@toto.fr';
        $changeMail->password = 'plain_password';

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->passwordEncoder->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithNoPassword()
    {
        $this->expectException(InvalidPasswordException::class);

        $this->passwordEncoder->encode($this->user, null)->shouldBeCalled()->willReturn('__ENCRYPTED_PASSWORD__');

        // Base
        $changeMail = new ChangeMail($this->user, $this->event);
        $changeMail->mail = null;
        $changeMail->password = null;

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->passwordEncoder->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithNoEmail()
    {
        $this->expectException(EmptyFieldException::class);

        $this->passwordEncoder->encode($this->user, 'plain_password')->shouldBeCalled()->willReturn('__TEST__');

        // Base
        $changeMail = new ChangeMail($this->user, $this->event);
        $changeMail->mail = null;
        $changeMail->password = 'plain_password';

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->passwordEncoder->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithSameEmail()
    {
        $this->expectException(SameEmailException::class);

        $this->passwordEncoder->encode($this->user, 'plain_password')->shouldBeCalled()->willReturn('__TEST__');

        // Base
        $changeMail = new ChangeMail($this->user, $this->event);
        $changeMail->mail = 'test@test.fr';
        $changeMail->password = 'plain_password';

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->passwordEncoder->reveal()
        );
        $handler->handle($changeMail);
    }

    public function testHandleWithEmailAlreadyExist()
    {
        $this->expectException(EmailAlreadyExistsException::class);
        $userExpected  = new User('test@test.fr', '__SALT__', '__TEST__', 'fr');

        $this->userRepository->findByEmail('toto@toto.fr')->shouldBeCalled()->willReturn($userExpected);
        $this->passwordEncoder->encode($this->user, 'plain_password')->shouldBeCalled()->willReturn('__TEST__');

        // Base
        $changeMail = new ChangeMail($this->user, $this->event);
        $changeMail->mail = 'toto@toto.fr';
        $changeMail->password = 'plain_password';

        // Handler
        $handler = new ChangeMailHandler(
            $this->userRepository->reveal(),
            $this->changeMailTokenRepository->reveal(),
            $this->tokenGenerator->reveal(),
            $this->eventDispatcher->reveal(),
            $this->passwordEncoder->reveal()
        );
        $handler->handle($changeMail);
    }
}
