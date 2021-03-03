<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening\Category;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Category\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\Category\CreateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Category\CategoryCreateType;
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
    private $authorizationChecker;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $request;

    public function setUp()
    {
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new CreateAction(
            $this->authorizationChecker->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->willReturn($configuration->reveal());
        $this->event->getLocales()->willReturn(['fr']);
        $configuration->getRightColor()->willReturn('#123213');
        $configuration->getLeftColor()->willReturn('#AABBCC');

        $create = new Create($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory->create(CategoryCreateType::class, $create, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(false);

        $this->twig
            ->render(CreateAction::TEMPLATE, ['event' => $this->event->reveal(), 'form' => $formView->reveal()])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new CreateAction(
            $this->authorizationChecker->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->willReturn($configuration->reveal());
        $this->event->getId()->willReturn(12);
        $this->event->getLocales()->willReturn(['fr']);
        $configuration->getRightColor()->willReturn('#123213');
        $configuration->getLeftColor()->willReturn('#AABBCC');

        $create = new Create($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory->create(CategoryCreateType::class, $create, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);

        $this->twig->render(Argument::any())->shouldNotBeCalled();
        $this->commandBus->handle($create)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.happening.category.create.success')->shouldBeCalled();

        $this->router->generate('admin_happening_category_list', ['event' => 12])->shouldBeCalled()->willReturn('/route');

        $action = new CreateAction(
            $this->authorizationChecker->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
