<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\UserTemporarilyDisabledEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security\AuthenticationFailureSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationFailureSubscriberTest extends TestCase
{
    /** @var ObjectProphecy|RequestStack */
    private $requestStack;

    /** @var ObjectProphecy|EventRepositoryInterface */
    private $eventRepository;

    /** @var ObjectProphecy|UserRepositoryInterface */
    private $userRepository;

    /** @var ObjectProphecy|DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $datetime;

    public function setup()
    {
        $this->requestStack = $this->prophesize(RequestStack::class);
        $masterRequest = $this->prophesize(Request::class);
        $masterRequest->getHost()->willReturn('hello.vimeet.proximum');
        $this->requestStack->getMasterRequest()->willReturn($masterRequest);

        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $this->datetime = new \DateTime();
    }

    public function testFirstFailWithExistingUser()
    {
        $authenticationFailureEvent = $this->prophesize(AuthenticationFailureEvent::class);

        $token = $this->prophesize(UsernamePasswordToken::class);
        $token->getProviderKey()->shouldBeCalled()->willReturn('main');

        $submittedUser = $this->prophesize(UserInterface::class);
        $token->getUser()->shouldBeCalled()->willReturn($submittedUser);

        $authenticationFailureEvent->getAuthenticationToken()->shouldBeCalled()->willReturn($token->reveal());

        $foundUser = $this->prophesize(User::class);
        $foundUser->updateLastFailedAuthentication($this->datetime)->shouldBeCalled();
        $foundUser->isTemporarilyDisabledDueToFailedAuthentication($this->datetime)->shouldBeCalled()->willReturn(false);

        $this->userRepository->findByEmail($submittedUser)->shouldBeCalled()->willReturn($foundUser->reveal());
        $this->userRepository->set($foundUser->reveal())->shouldBeCalled();

        $this->eventDispatcher->dispatch()->shouldNotBeCalled();

        $authenticationFailureSubscriber = new AuthenticationFailureSubscriber(
            $this->requestStack->reveal(),
            $this->eventRepository->reveal(),
            $this->userRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->datetime
        );

        $authenticationFailureSubscriber->processException($authenticationFailureEvent->reveal());
    }

    public function testBlockingFailWithExistingUser()
    {
        $authenticationFailureEvent = $this->prophesize(AuthenticationFailureEvent::class);

        $token = $this->prophesize(UsernamePasswordToken::class);
        $token->getProviderKey()->shouldBeCalled()->willReturn('main');

        $submittedUser = $this->prophesize(UserInterface::class);
        $token->getUser()->shouldBeCalled()->willReturn($submittedUser);

        $authenticationFailureEvent->getAuthenticationToken()->shouldBeCalled()->willReturn($token->reveal());

        $foundUser = $this->prophesize(User::class);
        $foundUser->updateLastFailedAuthentication($this->datetime)->shouldBeCalled();
        $foundUser->isTemporarilyDisabledDueToFailedAuthentication($this->datetime)->shouldBeCalled()->willReturn(true);
        $foundUser->getLocale()->willReturn('fr');

        $this->userRepository->findByEmail($submittedUser)->shouldBeCalled()->willReturn($foundUser->reveal());
        $this->userRepository->set($foundUser->reveal())->shouldBeCalled();

        $this->eventRepository->getEventByDomain('hello.vimeet.proximum')->willReturn($this->prophesize(Event::class));

        $this->eventDispatcher->dispatch(
            Events::USER_ACCOUNT_TEMPORARILY_DISABLED,
            Argument::type(UserTemporarilyDisabledEvent::class)
        )->shouldBeCalled();

        $authenticationFailureSubscriber = new AuthenticationFailureSubscriber(
            $this->requestStack->reveal(),
            $this->eventRepository->reveal(),
            $this->userRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->datetime
        );

        $authenticationFailureSubscriber->processException($authenticationFailureEvent->reveal());
    }
}
