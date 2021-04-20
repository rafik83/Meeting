<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\PrepareIndex;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\IndexAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\IndexType;
use Twig\Environment;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class IndexActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $request;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->request = $this->prophesize(Request::class);
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $command = new PrepareIndex();
        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $this->formFactory->create(IndexType::class, $command)->shouldBeCalled()->willReturn($form->reveal());
        $this->twig
            ->render('AdminBundle:Event:index.html.twig', [
                'form' => $formView->reveal(),
            ])->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new IndexAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $result = $action($this->request->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testInvokeHandle()
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $command = new PrepareIndex();
        $form = $this->prophesize(Form::class);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $this->formFactory->create(IndexType::class, $command)->shouldBeCalled()->willReturn($form->reveal());
        $this->twig->render(Argument::any())->shouldNotBeCalled();
        $this->commandBus->handle($command)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.event.index.success')->shouldBeCalled();
        $this->router->generate('admin_event_list')->shouldBeCalled()->willReturn('/event');

        $action = new IndexAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $result = $action($this->request->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
