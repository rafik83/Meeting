<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Event\Design;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\Design\UpdateDesign;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\Design\UpdateDesignAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Design\DesignType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateDesignActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationChecker;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $configuration;

    /** @var Request */
    private $request;

    public function setUp()
    {
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);

        $this->event = $this->prophesize(Event::class);
        $this->configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->willReturn($this->configuration->reveal());
        $this->request = new Request();
    }

    public function testInvokeNoAccess()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new UpdateDesignAction(
            $this->authorizationChecker->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );

        $action($this->request, $this->event->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->event->getLocales()->willReturn(['fr', 'en', 'de']);
        $this->configuration->getHeaderLeftColor()->willReturn('#AAAAAA');
        $this->configuration->getHeaderRightColor()->willReturn('#BBBBBB');
        $this->configuration->getLeftColor()->willReturn('#CCCCCC');
        $this->configuration->getRightColor()->willReturn('#DDDDDD');
        $this->configuration->getTextColor()->willReturn('#EEEEEE');
        $this->configuration->getHeaderButtonLeftColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonRightColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonTextColor()->willReturn('#FFFFFF');
        $this->configuration->getBackgroundColor()->willReturn('#FFFFFF');


        $command = new UpdateDesign($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());

        $this->formFactory->create(DesignType::class, $command, ['submit' => true, 'event' => $this->event->reveal()])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->engine->renderResponse('AdminBundle:Event/Design:updateDesign.html.twig', [
            'event' => $this->event->reveal(),
            'form' => $formView->reveal()
        ])->shouldBeCalled()
            ->willReturn(new Response());

        $action = new UpdateDesignAction(
            $this->authorizationChecker->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );

        $result = $action($this->request, $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testInvokeHandle(): void
    {
        $this->authorizationChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->event->getLocales()->willReturn(['fr', 'en', 'de']);
        $this->event->getId()->willReturn(12);
        $this->configuration->getHeaderLeftColor()->willReturn('#AAAAAA');
        $this->configuration->getHeaderRightColor()->willReturn('#BBBBBB');
        $this->configuration->getLeftColor()->willReturn('#CCCCCC');
        $this->configuration->getRightColor()->willReturn('#DDDDDD');
        $this->configuration->getTextColor()->willReturn('#EEEEEE');
        $this->configuration->getHeaderButtonLeftColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonRightColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonTextColor()->willReturn('#FFFFFF');
        $this->configuration->getBackgroundColor()->willReturn('#FFFFFF');


        $command = new UpdateDesign($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $form->createView()->shouldNotBeCalled();

        $this->formFactory->create(DesignType::class, $command, ['submit' => true, 'event' => $this->event->reveal()])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();
        $this->commandBus->handle($command)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.event.design.update.success')->shouldBeCalled();
        $this->router->generate('admin_event_design_update', ['event' => 12])
            ->shouldBeCalled()
            ->willReturn('path/to/route')
        ;

        $action = new UpdateDesignAction(
            $this->authorizationChecker->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );

        $result = $action($this->request, $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
