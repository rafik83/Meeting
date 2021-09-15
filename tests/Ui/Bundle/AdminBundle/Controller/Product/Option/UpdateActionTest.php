<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Product\Option;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Product\Option\UpdateOption;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Option\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Option\UpdateOptionType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

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

    /** @var Request */
    private $request;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $product;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);

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
        $this->product->isAttributable()->willReturn(false);
        $this->product->isUpdatable()->willReturn(true);
        $this->product->getDeletableUntil()->willReturn(null);
        $this->product->getBuyableUntil()->willReturn(null);
        $this->product->isSubjectedToValidation()->willReturn(false);
        $this->product->canScanParticipant()->willReturn(false);
        $this->product->getHappenings()->willReturn([]);
    }

    public function testIsGranted(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->happeningRepository->reveal()
        );
        $action($this->request, $this->event->reveal(), $this->product->reveal());
    }

    public function testProductEvent(): void
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
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->happeningRepository->reveal()
        );
        $action($this->request, $event->reveal(), $this->product->reveal());
    }


    public function testProductType(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->product->isOption()->willReturn(false);

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->happeningRepository->reveal()
        );
        $action($this->request, $this->event->reveal(), $this->product->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->product->isOption()->willReturn(true);

        $update = new UpdateOption($this->product->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(UpdateOptionType::class, $update, [
                'product'  => $this->product->reveal(),
                'locale' => 'fr',
                'submit' => true,
                'happenings' => []
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $formView = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->happeningRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $this->twig
            ->render('AdminBundle:Product:updateOption.html.twig', [
                'form' => $formView->reveal(),
                'event' => $this->event->reveal(),
                'product' => $this->product->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->happeningRepository->reveal()
        );
        $result = $action($this->request, $this->event->reveal(), $this->product->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->product->isOption()->willReturn(true);

        $update = new UpdateOption($this->product->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(UpdateOptionType::class, $update, [
                'product'  => $this->product->reveal(),
                'locale' => 'fr',
                'submit' => true,
                'happenings' => []
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
        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $this->happeningRepository->findByEvent($this->event)->shouldBeCalled()->willReturn([]);

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->happeningRepository->reveal()
        );
        $result = $action($this->request, $this->event->reveal(), $this->product->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
