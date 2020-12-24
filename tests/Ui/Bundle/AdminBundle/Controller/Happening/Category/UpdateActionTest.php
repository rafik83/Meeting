<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening\Category;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Category\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\Category\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Category\CategoryUpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $engine;

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

    /** @var ObjectProphecy */
    private $category;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
        $this->category = $this->prophesize(Category::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->category->reveal());
    }

    public function testEventNotTheSame()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $event = $this->prophesize(Event::class);
        $this->category->getEvent()->willReturn($event->reveal());

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->category->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->category->getEvent()->willReturn($this->event->reveal());

        $configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->willReturn($configuration->reveal());
        $this->event->getId()->willReturn(12);
        $this->event->getLocales()->willReturn(['fr']);
        $this->category->getPicto()->willReturn('picto');
        $this->category->getRank()->willReturn(1);
        $this->category->getLeftColor()->willReturn('#123123');
        $this->category->getRightColor()->willReturn('#321321');
        $this->category->getTitle('fr')->willReturn('title');

        $update = new Update($this->category->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory->create(CategoryUpdateType::class, $update, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(false);

        $this->engine
            ->renderResponse(UpdateAction::TEMPLATE, ['event' => $this->event->reveal(), 'form' => $formView->reveal()])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->category->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->category->getEvent()->willReturn($this->event->reveal());

        $configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->willReturn($configuration->reveal());
        $this->event->getId()->willReturn(12);
        $this->event->getLocales()->willReturn(['fr']);
        $this->category->getPicto()->willReturn('picto');
        $this->category->getRank()->willReturn(1);
        $this->category->getLeftColor()->willReturn('#123123');
        $this->category->getRightColor()->willReturn('#321321');
        $this->category->getTitle('fr')->willReturn('title');

        $update = new Update($this->category->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory->create(CategoryUpdateType::class, $update, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);

        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();
        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.happening.category.update.success')->shouldBeCalled();

        $this->router
            ->generate('admin_happening_category_list', ['event' => 12])
            ->shouldBeCalled()
            ->willReturn('/route')
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->category->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
