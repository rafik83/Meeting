<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\AdminTemporarilyDisabledEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security\AdminAuthenticationFailureSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminAuthenticationFailureSubscriberTest extends TestCase
{
    /** @var ObjectProphecy|RequestStack */
    private $requestStack;

    /** @var ObjectProphecy|AdminRepositoryInterface */
    private $adminRepository;

    /** @var ObjectProphecy|DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $datetime;

    public function setup()
    {
        $this->requestStack = $this->prophesize(RequestStack::class);
        $masterRequest = $this->prophesize(Request::class);
        $this->requestStack->getMasterRequest()->willReturn($masterRequest);

        $this->adminRepository = $this->prophesize(AdminRepositoryInterface::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $this->datetime = new \DateTime();
    }

    public function testFirstFailWithExistingAdmin()
    {
        $authenticationFailureEvent = $this->prophesize(AuthenticationFailureEvent::class);

        $token = $this->prophesize(UsernamePasswordToken::class);
        $token->getProviderKey()->shouldBeCalled()->willReturn('admin');

        $submittedUser = $this->prophesize(UserInterface::class);
        $token->getUser()->shouldBeCalled()->willReturn($submittedUser);

        $authenticationFailureEvent->getAuthenticationToken()->shouldBeCalled()->willReturn($token->reveal());

        $foundAdmin = $this->prophesize(Admin::class);
        $foundAdmin->updateLastFailedAuthentication($this->datetime)->shouldBeCalled();
        $foundAdmin->isTemporarilyDisabledDueToFailedAuthentication($this->datetime)->shouldBeCalled()->willReturn(false);

        $this->adminRepository->findByEmail($submittedUser)->shouldBeCalled()->willReturn($foundAdmin->reveal());
        $this->adminRepository->set($foundAdmin->reveal())->shouldBeCalled();

        $this->eventDispatcher->dispatch()->shouldNotBeCalled();

        $authenticationFailureSubscriber = new AdminAuthenticationFailureSubscriber(
            $this->requestStack->reveal(),
            $this->adminRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->datetime
        );

        $authenticationFailureSubscriber->processException($authenticationFailureEvent->reveal());
    }

    public function testBlockingFailWithExistingAdmin()
    {
        $authenticationFailureEvent = $this->prophesize(AuthenticationFailureEvent::class);

        $token = $this->prophesize(UsernamePasswordToken::class);
        $token->getProviderKey()->shouldBeCalled()->willReturn('admin');

        $submittedUser = $this->prophesize(UserInterface::class);
        $token->getUser()->shouldBeCalled()->willReturn($submittedUser);

        $authenticationFailureEvent->getAuthenticationToken()->shouldBeCalled()->willReturn($token->reveal());

        $foundAdmin = $this->prophesize(Admin::class);
        $foundAdmin->updateLastFailedAuthentication($this->datetime)->shouldBeCalled();
        $foundAdmin->isTemporarilyDisabledDueToFailedAuthentication($this->datetime)->shouldBeCalled()->willReturn(true);
        $foundAdmin->getLocale()->willReturn('fr');

        $this->adminRepository->findByEmail($submittedUser)->shouldBeCalled()->willReturn($foundAdmin->reveal());
        $this->adminRepository->set($foundAdmin->reveal())->shouldBeCalled();

        $this->eventDispatcher->dispatch(
            Events::ADMIN_ACCOUNT_TEMPORARILY_DISABLED,
            Argument::type(AdminTemporarilyDisabledEvent::class)
        )->shouldBeCalled();

        $authenticationFailureSubscriber = new AdminAuthenticationFailureSubscriber(
            $this->requestStack->reveal(),
            $this->adminRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->datetime
        );

        $authenticationFailureSubscriber->processException($authenticationFailureEvent->reveal());
    }
}
