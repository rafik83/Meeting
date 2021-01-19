<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\AvailabilityTimeRange;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\AvailabilityTimeRange\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\AvailabilityTimeRange\CreateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\AvailabilityTimeRange\CreateType;
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
    private $authorizationChecker;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $event;

    /** @var Request */
    private $request;

    public function setUp()
    {
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = new Request();
    }

    public function testInvokeNotAuthorized()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);

        $action = new CreateAction(
            $this->authorizationChecker->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal()
        );

        $action($this->request, $this->event->reveal());
    }

    public function testInvokeWithoutDays()
    {
        $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->event->hasDay()->willReturn(false);
        $this->event->getId()->willReturn(1);

        $this->flashBag
            ->add('error', 'flash.admin.availabilityTimeRange.eventHasNoDay')
            ->shouldBeCalled()
        ;

        $this->router
            ->generate('admin_event_availability_time_range_list', ['event' => 1])
            ->shouldBeCalled()
            ->willReturn('/path/to/route')
        ;

        $action = new CreateAction(
            $this->authorizationChecker->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($this->request, $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testInvoke()
    {
        $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->event->hasDay()->willReturn(true);
        $this->event->getTimeZone()->willReturn('Europe/Paris');

        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();

        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 10:00:00.000');
        $day = new Event\Day($this->event->reveal(), $begin, $end);
        $this->event->getFirstDay()->willReturn($day);
        $this->event->getLastDay()->willReturn($day);

        $create = new Create($this->event->reveal());

        $form = $this->prophesize(Form::class);
        $this->formFactory->create(CreateType::class, $create, ['timezone' => 'Europe/Paris', 'submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $formView = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());

        $response = new Response();
        $this->engine
            ->renderResponse('AdminBundle:AvailabilityTimeRange:create.html.twig', [
                'event' => $this->event->reveal(),
                'form' => $formView->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $action = new CreateAction(
            $this->authorizationChecker->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($this->request, $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testInvokeHandle()
    {
        $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->event->hasDay()->willReturn(true);
        $this->event->getId()->willReturn(1);
        $this->event->getTimeZone()->willReturn('Europe/Paris');

        $begin = new \DateTime('2017-10-10 10:00:00.000');
        $end = new \DateTime('2017-10-10 10:00:00.000');
        $day = new Event\Day($this->event->reveal(), $begin, $end);
        $this->event->getFirstDay()->willReturn($day);
        $this->event->getLastDay()->willReturn($day);

        $create = new Create($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory->create(CreateType::class, $create, ['timezone' => 'Europe/Paris', 'submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $form->createView()->shouldNotBeCalled();

        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();
        $this->commandBus->handle($create)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.availabilityTimeRange.create.success')->shouldBeCalled();
        $this->router
            ->generate('admin_event_availability_time_range_list', ['event' => 1])
            ->shouldBeCalled()
            ->willReturn('/path/to/route')
        ;

        $action = new CreateAction(
            $this->authorizationChecker->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($this->request, $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
