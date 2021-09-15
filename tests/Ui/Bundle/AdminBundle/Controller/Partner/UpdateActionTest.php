<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Partner;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Partner\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Partner\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter,
        $eventRepository,
        $formFactory,
        $commandBus,
        $flashBag,
        $router,
        $twig,
        $errorFactory,
        $request,
        $admin,
        $partner
    ;
    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->errorFactory = $this->prophesize(ErrorFactory::class);
        $this->request = $this->prophesize(Request::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->partner = $this->prophesize(Admin::class);
    }

    public function testAuthorization(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->eventRepository->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->errorFactory->reveal()
        );

        $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->partner->reveal());
    }

    public function testAuthorizationNotPartner(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->partner->isPartner()->shouldBeCalled()->willReturn(false);

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->eventRepository->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->errorFactory->reveal()
        );

        $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->partner->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->admin->isSuperAdmin()->shouldBeCalled()->willReturn(true);
        $this->partner->isPartner()->shouldBeCalled()->willReturn(true);
        $this->partner->getEmail()->willReturn('test@example.net');
        $this->partner->getLastname()->willReturn('Test');
        $this->partner->getFirstname()->willReturn('Test');
        $this->partner->getAllowedTypes()->shouldBeCalled()->willReturn([]);

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $events = [];
        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $this->eventRepository->getEventsByAdmin($this->admin->reveal())->shouldBeCalled()->willReturn($events);
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->formFactory
            ->create(UpdateType::class, Argument::type(Update::class), ['submit' => true, 'events' => $events, 'locale' => 'fr'])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->twig
            ->render('AdminBundle:Partner:update.html.twig', ['form' => $view, 'isSuperAdmin' => true])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->eventRepository->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->partner->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->admin->isSuperAdmin()->shouldBeCalled()->willReturn(true);
        $this->partner->isPartner()->shouldBeCalled()->willReturn(true);
        $this->partner->getEmail()->willReturn('test@example.net');
        $this->partner->getLastname()->willReturn('Test');
        $this->partner->getFirstname()->willReturn('Test');
        $this->partner->getAllowedTypes()->shouldBeCalled()->willReturn([]);

        $events = [];
        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $this->eventRepository->getEventsByAdmin($this->admin->reveal())->shouldBeCalled()->willReturn($events);
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(UpdateType::class, Argument::type(Update::class), ['submit' => true, 'events' => $events, 'locale' => 'fr'])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->router->generate('admin_list_admin')
            ->shouldBeCalled()
            ->willReturn('/admin')
        ;

        $this->commandBus
            ->handle(Argument::type(Update::class))
            ->shouldBeCalled()
        ;

        $this->flashBag
            ->add('success', 'flash.admin.partner.update.success')
            ->shouldBeCalled()
        ;

        $this->twig
            ->render('AdminBundle:Partner:update.html.twig', Argument::any())
            ->shouldNotBeCalled()
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->eventRepository->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->partner->reveal());
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/admin', $result->getTargetUrl());
    }

    public function testHandleException(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->admin->isSuperAdmin()->shouldBeCalled()->willReturn(true);
        $this->partner->isPartner()->shouldBeCalled()->willReturn(true);
        $this->partner->getEmail()->willReturn('test@example.net');
        $this->partner->getLastname()->willReturn('Test');
        $this->partner->getFirstname()->willReturn('Test');
        $this->partner->getAllowedTypes()->shouldBeCalled()->willReturn([]);

        $events = [];
        $this->eventRepository->getEventsByAdmin($this->admin->reveal())->shouldBeCalled()->willReturn($events);
        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $this->formFactory
            ->create(UpdateType::class, Argument::type(Update::class), ['submit' => true, 'events' => $events, 'locale' => 'fr'])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->router->generate('admin_list_admin')
            ->shouldNotBeCalled()
        ;

        $exception = new EmailAlreadyExistsException();
        $this->commandBus
            ->handle(Argument::type(Update::class))
            ->shouldBeCalled()
            ->willThrow($exception)
        ;

        $this->flashBag
            ->add('success', 'flash.admin.partner.update.success')
            ->shouldNotBeCalled()
        ;

        $error = new FormError('error');
        $this->errorFactory->create('validators.emailAlreadyExist', 'fr')
            ->shouldBeCalled()
            ->willReturn($error)
        ;

        $form->get('email')->shouldBeCalled()->willReturn($form);
        $form->addError($error)->shouldBeCalled();

        $this->twig
            ->render('AdminBundle:Partner:update.html.twig', ['form' => $view, 'isSuperAdmin' => true])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->eventRepository->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->partner->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }
}
