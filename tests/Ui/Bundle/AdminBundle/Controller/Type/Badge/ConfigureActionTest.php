<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Type\Badge;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Type\Badge\Configure;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\Badge\ConfigureAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Badge\ConfigureType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ConfigureActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $badgeRepository;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $type;

    /** @var ObjectProphecy */
    private $event;

    /** @var Request */
    private $request;

    /** @var ObjectProphecy|TranslatorInterface */
    private $translator;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);

        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);
        $this->request = new Request();
        $this->request->setLocale('fr');
    }

    public function testAccessOnEvent(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new ConfigureAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->badgeRepository->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->translator->reveal()
        );

        $action($this->request, $this->event->reveal(), $this->type->reveal());
    }

    public function testAccessOnType(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $event = $this->prophesize(Event::class);
        $this->type->getEvent()->willReturn($event->reveal());

        $action = new ConfigureAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->badgeRepository->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->translator->reveal()
        );

        $action($this->request, $this->event->reveal(), $this->type->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->type->getEvent()->willReturn($this->event->reveal());
        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $configure = new Configure($this->event->reveal(), $this->type->reveal(), null);

        $form = $this->prophesize(Form::class);
        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(false);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($formView);
        $this->formFactory
            ->create(ConfigureType::class, $configure, [
                'type' => $this->type->reveal(),
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('en');

        $this->twig
            ->render('AdminBundle:Type/Badge:configure.html.twig', [
                'event' => $this->event->reveal(),
                'badge' => null,
                'type' => $this->type->reveal(),
                'form' => $formView->reveal(),
                'locale' => 'en',
            ])
            ->shouldBeCalled()
            ->willReturn(new Response());

        $action = new ConfigureAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->badgeRepository->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->translator->reveal()
        );

        $result = $action($this->request, $this->event->reveal(), $this->type->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->type->getEvent()->willReturn($this->event->reveal());
        $this->badgeRepository->findByType($this->type->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $configure = new Configure($this->event->reveal(), $this->type->reveal(), null);

        $form = $this->prophesize(Form::class);
        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);
        $this->formFactory
            ->create(ConfigureType::class, $configure, [
                'type' => $this->type->reveal(),
                'submit' => true,
            ])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->commandBus->handle($configure)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.type.badge.configuration.success')->shouldBeCalled();
        $this->event->getId()->willReturn(12);
        $this->type->getId()->willReturn(14);
        $this->router->generate('admin_type_badge_configuration', ['event' => 12, 'type' => 14])
            ->shouldBeCalled()
            ->willReturn('path/to/route')
        ;

        $this->twig
            ->render(Argument::any())
            ->shouldNotBeCalled()
        ;

        $action = new ConfigureAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->badgeRepository->reveal(),
            $this->commandBus->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->translator->reveal()
        );

        $result = $action($this->request, $this->event->reveal(), $this->type->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
