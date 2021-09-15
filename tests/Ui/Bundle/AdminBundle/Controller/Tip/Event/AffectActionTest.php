<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event\AffectAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\AffectType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AffectActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $twig;

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
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->twig = $this->prophesize(Environment::class);
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
        $this->twig->render(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $action = new AffectAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->admin->reveal());
    }

    public function testAccessDeniedEventPermissions()
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
        $this->twig->render(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $action = new AffectAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->admin->reveal());
    }

    public function testInvoke()
    {
        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $affect = new Affect($this->event->reveal());
        $this->formFactory
            ->create(
                AffectType::class,
                $affect,
                [
                    'admin' => $this->admin->reveal(),
                    'event' => $this->event->reveal(),
                    'locale' => 'fr',
                    'submit' => true,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;
        $response = new Response('');
        $this->twig
            ->render(AffectAction::TEMPLATE, [
                'event' => $this->event->reveal(),
                'form' => $formView->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn('')
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $action = new AffectAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->admin->reveal());

        $this->assertEquals($response, $result);
    }

    public function testHandle()
    {
        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $this->event->getId()->willReturn(12);
        $form = $this->prophesize(Form::class);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $affect = new Affect($this->event->reveal());
        $this->formFactory
            ->create(
                AffectType::class,
                $affect,
                [
                    'admin' => $this->admin->reveal(),
                    'event' => $this->event->reveal(),
                    'locale' => 'fr',
                    'submit' => true,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;
        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $this->commandBus->handle($affect)->shouldBeCalled();
        $this->router->generate('admin_tip_event_list', ['event' => 12])->shouldBeCalled()->willReturn('/route');
        $this->flashBag->add('success', 'flash.admin.tip.affect.success')->shouldBeCalled();

        $action = new AffectAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        /** @var RedirectResponse */
        $result = $action($this->request->reveal(), $this->event->reveal(), $this->admin->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
