<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Product\Export;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Export\ExportAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;
    
    /** @var ObjectProphecy */
    private $commandBus;
    
    /** @var ObjectProphecy */
    private $router;
    
    /** @var ObjectProphecy */
    private $flashBag;
    
    /** @var ObjectProphecy */
    private $event;
    
    /** @var ObjectProphecy */
    private $request;
    
    /** @var ObjectProphecy */
    private $admin;
    
    /** @var ObjectProphecy */
    private $adminDomain;
    
    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
        $this->admin = $this->prophesize(UserInterface::class);
        $this->adminDomain = $this->prophesize(AdminDomain::class);
    }
    
    public function testAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);
        
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
    
        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        
        $action = new ExportAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal()
        );
        
        $action($this->request->reveal(), $this->admin->reveal(), $this->event->reveal(), $this->adminDomain->reveal());
    }
}
