<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\PaymentConditions\Update;
use Proximum\Vimeet\Application\Query\Type\TypesWithPaymentConditionsViewQuery;
use Proximum\Vimeet\Application\View\Type\TypesWithPaymentConditionsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\PaymentConditionsAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions\UpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PaymentConditionsActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
    }

    public function testAccessDeniedEventAccess()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new PaymentConditionsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->engine->reveal()
        );
        $action($this->request->reveal(), $this->event->reveal());
    }

    public function testAccessDeniedRole()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);

        $action = new PaymentConditionsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->engine->reveal()
        );
        $action($this->request->reveal(), $this->event->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);

        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->isAllowDeposit()->willReturn(true);
        $configuration->getDepositUntil()->willReturn(new \DateTime());
        $configuration->getDeposit()->willReturn(50);
        $configuration->getMinimumForDeposit()->willReturn(1000);
        $configuration->getPaymentModes()->willReturn([]);
        $this->event->getConfiguration()->willReturn($configuration->reveal());
        $update = new Update($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(false);

        $this->formFactory
            ->create(UpdateType::class, $update, [
                'event' => $this->event->reveal(),
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $query = new TypesWithPaymentConditionsViewQuery($this->event->reveal(), 'fr');
        $view = $this->prophesize(TypesWithPaymentConditionsView::class);
        $this->queryBus->handle($query)->shouldBeCalled()->willReturn($view->reveal());

        $this->engine->renderResponse(PaymentConditionsAction::TEMPLATE, [
                'event' => $this->event->reveal(),
                'form' => $formView->reveal(),
                'typeWithPaymentConditions' => $view->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new PaymentConditionsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->engine->reveal()
        );
        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);

        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->isAllowDeposit()->willReturn(true);
        $configuration->getDepositUntil()->willReturn(new \DateTime());
        $configuration->getDeposit()->willReturn(50);
        $configuration->getMinimumForDeposit()->willReturn(1000);
        $configuration->getPaymentModes()->willReturn([]);
        $this->event->getConfiguration()->willReturn($configuration->reveal());

        $update = new Update($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);

        $this->formFactory
            ->create(UpdateType::class, $update, [
                'event' => $this->event->reveal(),
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->event->getId()->willReturn(12);

        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.event.paymentConditions.update.success')->shouldBeCalled();
        $this->router
            ->generate('admin_event_payment_conditions', ['event' => 12])
            ->shouldBeCalled()
            ->willReturn('/route')
        ;
        $this->queryBus->handle(Argument::any())->shouldNotBeCalled();
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $action = new PaymentConditionsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->engine->reveal()
        );
        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
