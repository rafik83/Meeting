<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Create;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\CreateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\CreateType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class CreateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var array */
    private $defaultLocales;

    public function setUp()
    {
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->defaultLocales = ['fr', 'en'];
    }

    public function testInvokeAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $request = $this->prophesize(Request::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(false);

        $action = new CreateAction(
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->defaultLocales
        );

        $action($request->reveal());
    }

    public function testInvoke()
    {
        $request = $this->prophesize(Request::class);
        $create = new Create($this->defaultLocales);
        $form = $this->prophesize(Form::class);

        $form->handleRequest($request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $this->formFactory
            ->create(CreateType::class, $create, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->twig
            ->render(CreateAction::TEMPLATE, ['form' => $formView->reveal()])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new CreateAction(
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->defaultLocales
        );

        $result = $action($request->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $request = $this->prophesize(Request::class);
        $create = new Create($this->defaultLocales);
        $form = $this->prophesize(Form::class);

        $form->handleRequest($request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $form->createView()->shouldNotBeCalled();

        $this->commandBus->handle($create)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.tip.create.success')->shouldBeCalled();

        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $this->formFactory
            ->create(CreateType::class, $create, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->router->generate('admin_tip_list')->shouldBeCalled()->willReturn('route');
        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $action = new CreateAction(
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->defaultLocales
        );

        $result = $action($request->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('route', $result->getTargetUrl());
    }
}
