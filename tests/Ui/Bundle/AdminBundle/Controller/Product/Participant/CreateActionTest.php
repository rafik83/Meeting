<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Product\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Product\Participant\CreateParticipant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Participant\CreateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant\CreateParticipantType;
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
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $availabilityTimeRangeRepository;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $engine;

    /** @var Request */
    private $request;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->availabilityTimeRangeRepository = $this->prophesize(AvailabilityTimeRangeRepositoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);

        $this->request = new Request();
        $this->event = $this->prophesize(Event::class);
        $this->request->setLocale('de');
        $this->event->getVat()->willReturn(20);
        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->event->getAvailableLocale('de')->willReturn('fr');
    }

    public function testIsGranted()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );
        $action($this->request, $this->event->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->availabilityTimeRangeRepository->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $create = new CreateParticipant($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(CreateParticipantType::class, $create, [
                'event'  => $this->event->reveal(),
                'locale' => 'fr',
                'submit' => true,
                'availabilityTimeRanges' => [],
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $formView = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->engine
            ->renderResponse('AdminBundle:Product:createParticipant.html.twig', [
                'form' => $formView->reveal(),
                'event' => $this->event->reveal()
            ])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );
        $result = $action($this->request, $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->availabilityTimeRangeRepository->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $create = new CreateParticipant($this->event->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(CreateParticipantType::class, $create, [
                'event'  => $this->event->reveal(),
                'locale' => 'fr',
                'submit' => true,
                'availabilityTimeRanges' => [],
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->event->getId()->willReturn(1);
        $this->commandBus->handle($create)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.product.create.success')->shouldBeCalled();
        $this->router->generate('admin_product', ['event' => 1])->shouldBeCalled()->willReturn('/route/path');
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal()
        );
        $result = $action($this->request, $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
