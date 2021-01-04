<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\RemoveAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $authorizationChecker;

    public function setUp()
    {
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $tip = $this->prophesize(Tip::class);

        $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(false);
        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();

        $action = new RemoveAction(
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->authorizationChecker->reveal()
        );

        $action($tip->reveal());
    }

    public function testInvoke()
    {
        $tip = $this->prophesize(Tip::class);

        $remove = new Remove($tip->reveal());
        $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $this->commandBus->handle($remove)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.tip.remove.success')->shouldBeCalled();
        $this->router->generate('admin_tip_list')->shouldBeCalled()->willReturn('route');

        $action = new RemoveAction(
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->authorizationChecker->reveal()
        );

        $result = $action($tip->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('route', $result->getTargetUrl());
    }
}
