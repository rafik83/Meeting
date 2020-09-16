<?php


namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Event\ConfigureDates;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\DatesAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\ConfigureDatesType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DatesActionTest extends TestCase
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
    private $engine;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $event;

    public function setUp(): void
    {
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
    }

    public function testAccessDeniedEventAccess(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new DatesAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );
        $action($this->request->reveal(), $this->event->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn(null);
        $configuration->getHappeningsOpenDate()->shouldBeCalled()->willReturn(null);
        $configuration->getSchedulePublishDate()->shouldBeCalled()->willReturn(null);
        $configuration->getCloseMeetingRequestDate()->shouldBeCalled()->willReturn(null);
        $configuration->getCloseAnsweringMeetingRequestDate()->shouldBeCalled()->willReturn(null);
        $configuration->getSmsActivationDate()->shouldBeCalled()->willReturn(null);
        $configuration->getAgendaOnlineDate()->shouldBeCalled()->willReturn(null);
        $configuration->getRegistrationOpenDate()->shouldBeCalled()->willReturn(null);
        $configuration->getRegistrationCloseDate()->shouldBeCalled()->willReturn(null);
        $configuration->getNetworkingOpenDate()->shouldBeCalled()->willReturn(null);
        $configuration->getNetworkingCloseDate()->shouldBeCalled()->willReturn(null);
        $configuration->getEnableBadgeForParticipantDate()->shouldBeCalled()->willReturn(null);
        $configuration->getEnableVisioTestMenuButtonDate()->shouldBeCalled()->willReturn(null);
        $this->event->getConfiguration()->willReturn($configuration->reveal());

        $update = new ConfigureDates($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(false);

        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->formFactory
            ->create(ConfigureDatesType::class, $update, [
                'event' => $this->event->reveal(),
                'showDateNetworking' => false,
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();
        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');

        $this->engine
            ->render('AdminBundle:Event:dates.html.twig', [
                'event' => $this->event->reveal(),
                'form' => $formView->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn('')
        ;

        $action = new DatesAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );
        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $configuration = $this->prophesize(Event\Configuration::class);
        $configuration->getCatalogOnlineDate()->shouldBeCalled()->willReturn(null);
        $configuration->getHappeningsOpenDate()->shouldBeCalled()->willReturn(null);
        $configuration->getSchedulePublishDate()->shouldBeCalled()->willReturn(null);
        $configuration->getCloseMeetingRequestDate()->shouldBeCalled()->willReturn(null);
        $configuration->getCloseAnsweringMeetingRequestDate()->shouldBeCalled()->willReturn(null);
        $configuration->getSmsActivationDate()->shouldBeCalled()->willReturn(null);
        $configuration->getAgendaOnlineDate()->shouldBeCalled()->willReturn(null);
        $configuration->getRegistrationOpenDate()->shouldBeCalled()->willReturn(null);
        $configuration->getRegistrationCloseDate()->shouldBeCalled()->willReturn(null);
        $configuration->getNetworkingOpenDate()->shouldBeCalled()->willReturn(null);
        $configuration->getNetworkingCloseDate()->shouldBeCalled()->willReturn(null);
        $configuration->getEnableBadgeForParticipantDate()->shouldBeCalled()->willReturn(null);
        $configuration->getEnableVisioTestMenuButtonDate()->shouldBeCalled()->willReturn(null);
        $this->event->getConfiguration()->willReturn($configuration->reveal());

        $update = new ConfigureDates($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);

        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->formFactory
            ->create(ConfigureDatesType::class, $update, [
                'event' => $this->event->reveal(),
                'showDateNetworking' => false,
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->event->getId()->willReturn(12);

        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.event.configure_dates.success')->shouldBeCalled();
        $this->router
            ->generate('admin_event_configure_dates', ['event' => 12])
            ->shouldBeCalled()
            ->willReturn('/route')
        ;
        $this->engine->render(Argument::any())->shouldNotBeCalled();

        $action = new DatesAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );
        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
