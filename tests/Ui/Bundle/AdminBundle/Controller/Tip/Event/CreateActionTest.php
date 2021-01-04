<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Create;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event\CreateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\CreateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $admin;

    public function setUp()
    {
        $this->admin = $this->prophesize(Admin::class);
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
    }

    public function testAccessDeniedRole()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->formFactory->create(Argument::any())->shouldNotBeCalled();
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $action = new CreateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->admin->reveal());
    }

    public function testAccessDeniedPermissionEvent()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->formFactory->create(Argument::any())->shouldNotBeCalled();
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $action = new CreateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->admin->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->request->getLocale()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $create = new Create($this->event->reveal());

        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());

        $this->commandBus->handle($create)->shouldNotBeCalled();
        $this->flashBag->add('success', 'flash.admin.tip.event.create.success')->shouldNotBeCalled();

        $this->formFactory
            ->create(
                CreateType::class,
                $create,
                [
                    'submit' => true,
                    'admin' => $this->admin->reveal(),
                    'event' => $this->event->reveal(),
                    'locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $response = new Response();
        $this->engine->renderResponse(
            CreateAction::TEMPLATE,
            [
                'event' => $this->event->reveal(),
                'form' => $formView->reveal(),
            ]
        )->shouldBeCalled()
        ->willReturn($response);

        $action = new CreateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->admin->reveal());

        $this->assertEquals($response, $result);
    }

    public function testHandle()
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $this->request->getLocale()->willReturn('fr');
        $create = new Create($this->event->reveal());
        $this->event->getId()->willReturn(12);
        $form = $this->prophesize(Form::class);

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $form->createView()->shouldNotBeCalled();

        $this->commandBus->handle($create)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.tip.event.create.success')->shouldBeCalled();

        $this->formFactory
            ->create(
                CreateType::class,
                $create,
                [
                    'submit' => true,
                    'admin' => $this->admin->reveal(),
                    'event' => $this->event->reveal(),
                    'locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();
        $this->router->generate('admin_tip_event_list', ['event' => 12])->shouldBeCalled()->willReturn('route');

        $action = new CreateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->admin->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('route', $result->getTargetUrl());
    }
}
