<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Impersonate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Impersonate\ImpersonateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ImpersonateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $impersonate;

    /** @var ObjectProphecy */
    private $eventUrlGenerator;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $admin;

    /** @var AdminDomain */
    private $adminDomain;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->impersonate = $this->prophesize(Impersonate::class);
        $this->eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);

        $this->request = $this->prophesize(Request::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->adminDomain = new AdminDomain($this->admin->reveal());
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new ImpersonateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->impersonate->reveal(),
            $this->eventUrlGenerator->reveal()
        );

        $action($this->request->reveal(), $this->adminDomain, $this->event->reveal(), $this->user->reveal());

    }

    public function testAccessDeniedWhenAllowedToSwitchRoleIsNotGranted()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_SWITCH')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new ImpersonateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->impersonate->reveal(),
            $this->eventUrlGenerator->reveal()
        );

        $action($this->request->reveal(), $this->adminDomain, $this->event->reveal(), $this->user->reveal());

    }

    public function testHandle()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_SWITCH')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $query = $this->prophesize(ParameterBag::class);
        $query->get('route')->shouldBeCalled()->willReturn('target_route');
        $query->get('params')->shouldBeCalled()->willReturn([]);
        $this->request->query = $query;

        $this->impersonate
            ->getEncodedToken($this->admin->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn('=encoded_token=')
        ;

        $this->eventUrlGenerator
            ->generateEventAbsoluteUrl($this->event->reveal(), 'target_route', [ '_switchto' => '=encoded_token=' ])
            ->shouldBeCalled()
            ->willReturn('https://event.wimeet.proximum/sample/path')
        ;

        $action = new ImpersonateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->impersonate->reveal(),
            $this->eventUrlGenerator->reveal()
        );

        $result = $action($this->request->reveal(), $this->adminDomain, $this->event->reveal(), $this->user->reveal());

        $this->assertEquals('https://event.wimeet.proximum/sample/path', $result->getTargetUrl());
    }

    public function testFailWhenRouteParamIsMissing()
    {
        $this->expectException(BadRequestHttpException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_SWITCH')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $query = $this->prophesize(ParameterBag::class);
        $query->get('route')->shouldBeCalled()->willReturn(null);
        $query->get('params')->shouldNotBeCalled();
        $this->request->query = $query;

        $action = new ImpersonateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->impersonate->reveal(),
            $this->eventUrlGenerator->reveal()
        );

        $action($this->request->reveal(), $this->adminDomain, $this->event->reveal(), $this->user->reveal());
    }
}
