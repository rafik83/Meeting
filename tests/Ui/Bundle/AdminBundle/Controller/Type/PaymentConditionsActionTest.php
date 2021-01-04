<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Type;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\PaymentConditions\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\PaymentConditionsAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\PaymentConditions\UpdateType;
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
    private $engine;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $type;

    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
    }

    public function testAccessDeniedRole()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new PaymentConditionsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->type->reveal());
    }

    public function testAccessDeniedEventAccess()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new PaymentConditionsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->type->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->type->getPaymentConditions()->willReturn(null);
        $this->type->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $this->event->getBankInfo('fr')->shouldBeCalled()->willReturn('bank info');
        $this->event->getBillingAddress('fr')->shouldBeCalled()->willReturn('billing address');
        $this->event->getPaymentCondition('fr')->shouldBeCalled()->willReturn('payment condition');
        $this->event->getPaymentFooter('fr')->shouldBeCalled()->willReturn('payment footer');

        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $update = new Update($this->type->reveal());
        $this->formFactory
            ->create(UpdateType::class, $update, [
                'event' => $this->event->reveal(),
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->request->getLocale()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->willReturn('de');
        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->engine
            ->renderResponse(PaymentConditionsAction::TEMPLATE, [
                'locale' => 'de',
                'event'  => $this->event->reveal(),
                'type'   => $this->type->reveal(),
                'form'   => $formView->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new PaymentConditionsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->type->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->type->getPaymentConditions()->willReturn(null);
        $this->type->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $this->event->getBankInfo('fr')->shouldBeCalled()->willReturn('bank info');
        $this->event->getBillingAddress('fr')->shouldBeCalled()->willReturn('billing address');
        $this->event->getPaymentCondition('fr')->shouldBeCalled()->willReturn('payment condition');
        $this->event->getPaymentFooter('fr')->shouldBeCalled()->willReturn('payment footer');

        $form = $this->prophesize(Form::class);
        $update = new Update($this->type->reveal());
        $this->formFactory
            ->create(UpdateType::class, $update, [
                'event' => $this->event->reveal(),
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->request->getLocale()->willReturn('fr');
        $this->event->getId()->willReturn(15);
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();
        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.type.paymentConditions.updated')->shouldBeCalled();
        $this->router
            ->generate('admin_type_list', ['event' => 15])
            ->shouldBeCalled()
            ->willReturn('/route')
        ;

        $action = new PaymentConditionsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->type->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
