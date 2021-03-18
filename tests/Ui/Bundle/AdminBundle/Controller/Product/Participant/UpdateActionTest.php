<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Product\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Participant\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant\UpdateParticipantType;
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

    /** @var ObjectProphecy */
    private $product;

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
        $this->product = $this->prophesize(Product::class);
        $this->product->getEvent()->willReturn($this->event->reveal());

        $this->product->getName()->willReturn('name');
        $this->product->getQuantityMax()->willReturn(5);
        $this->product->getUnitPrice()->willReturn(20);
        $this->product->getVat()->willReturn(20);
        $this->product->getAvailabilityCurrent()->willReturn(12);
        $this->product->getAvailabilityMax()->willReturn(20);
        $this->product->getAvailabilityTimeRanges()->willReturn([]);

        $this->product->getTitle('fr')->willReturn('title fr');
        $this->product->getHeading('fr')->willReturn('heading fr');
        $this->product->getDescription('fr')->willReturn('description fr');
        $this->product->getAddon('fr')->willReturn('addon fr');
        $this->product->getSubjectedToValidationHelp('fr')->willReturn('help fr');
        $this->product->getTitle('en')->willReturn('title en');
        $this->product->getHeading('en')->willReturn('heading en');
        $this->product->getDescription('en')->willReturn('description en');
        $this->product->getAddon('en')->willReturn('addon en');
        $this->product->getSubjectedToValidationHelp('en')->willReturn('help en');
    }

    public function testIsGranted()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal()
        );
        $action($this->request, $this->event->reveal(), $this->product->reveal());
    }

    public function testProductEvent()
    {
        $this->expectException(AccessDeniedException::class);
        $event = $this->prophesize(Event::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal()
        );
        $action($this->request, $event->reveal(), $this->product->reveal());
    }


    public function testProductType()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->product->isParticipant()->willReturn(false);

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal()
        );
        $action($this->request, $this->event->reveal(), $this->product->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->product->isParticipant()->willReturn(true);

        $this->availabilityTimeRangeRepository->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $update = new UpdateParticipant($this->product->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(UpdateParticipantType::class, $update, [
                'product'  => $this->product->reveal(),
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
            ->renderResponse('AdminBundle:Product:updateParticipant.html.twig', [
                'form' => $formView->reveal(),
                'event' => $this->event->reveal(),
                'product' => $this->product->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal()
        );
        $result = $action($this->request, $this->event->reveal(), $this->product->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->product->isParticipant()->willReturn(true);

        $this->availabilityTimeRangeRepository->findByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $update = new UpdateParticipant($this->product->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(UpdateParticipantType::class, $update, [
                'product'  => $this->product->reveal(),
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
        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.product.update.success')->shouldBeCalled();
        $this->router->generate('admin_product', ['event' => 1])->shouldBeCalled()->willReturn('/route/path');
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->availabilityTimeRangeRepository->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal()
        );
        $result = $action($this->request, $this->event->reveal(), $this->product->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
