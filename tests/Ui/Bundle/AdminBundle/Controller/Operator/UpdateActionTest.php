<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Operator;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Operator\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Operator\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator\UpdateType;
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
        $formFactory,
        $commandBus,
        $flashBag,
        $router,
        $twig,
        $errorFactory,
        $request,
        $admin,
        $operator
    ;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->errorFactory = $this->prophesize(ErrorFactory::class);
        $this->request = $this->prophesize(Request::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->operator = $this->prophesize(Admin::class);
    }

    public function testAuthorization(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ORGANIZER')
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
            $this->errorFactory->reveal()
        );

        $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->operator->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ORGANIZER')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $events = new ArrayCollection();
        $this->admin->getEvents()->shouldBeCalled()->willReturn($events);
        $this->operator->getEvents()->shouldBeCalled()->willReturn($events);
        $this->operator->getEmail()->willReturn('test@example.net');
        $this->operator->getLastname()->willReturn('Test');
        $this->operator->getFirstname()->willReturn('Test');
        $this->operator->getRole()->willReturn('ROLE_ORGANIZER');
        $this->operator->isOperator()->shouldBeCalled()->willReturn(true);
        $update = new Update($this->operator->reveal(), $events->toArray());
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->formFactory
            ->create(UpdateType::class, $update, ['submit' => true, 'events' => $events->toArray()])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->twig
            ->render('AdminBundle:Operator:update.html.twig', ['form' => $view])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->operator->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ORGANIZER')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $events = new ArrayCollection();
        $this->admin->getEvents()->shouldBeCalled()->willReturn($events);
        $this->operator->getEvents()->shouldBeCalled()->willReturn($events);
        $this->operator->getEmail()->willReturn('test@example.net');
        $this->operator->getLastname()->willReturn('Test');
        $this->operator->getFirstname()->willReturn('Test');
        $this->operator->getRole()->willReturn('ROLE_OPERATOR');
        $this->operator->isOperator()->shouldBeCalled()->willReturn(true);
        $update = new Update($this->operator->reveal(), $events->toArray());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(UpdateType::class, $update, ['submit' => true, 'events' => $events->toArray()])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->router->generate('admin_list_operator')
            ->shouldBeCalled()
            ->willReturn('/operator')
        ;

        $this->commandBus
            ->handle($update)
            ->shouldBeCalled()
        ;

        $this->flashBag
            ->add('success', 'flash.admin.operator.update.success')
            ->shouldBeCalled()
        ;

        $this->twig
            ->render('AdminBundle:Operator:create.html.twig', Argument::any())
            ->shouldNotBeCalled()
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->errorFactory->reveal()
        );

        /** @var RedirectResponse */
        $result = $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->operator->reveal());
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/operator', $result->getTargetUrl());
    }

    public function testHandleException(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ORGANIZER')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $events = new ArrayCollection();
        $this->admin->getEvents()->shouldBeCalled()->willReturn($events);
        $this->operator->getEvents()->shouldBeCalled()->willReturn($events);
        $this->operator->getEmail()->willReturn('test@example.net');
        $this->operator->getLastname()->willReturn('Test');
        $this->operator->getFirstname()->willReturn('Test');
        $this->operator->getRole()->willReturn('ROLE_OPERATOR');
        $this->operator->isOperator()->shouldBeCalled()->willReturn(true);
        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $update = new Update($this->operator->reveal(), $events->toArray());
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $this->formFactory
            ->create(UpdateType::class, $update, ['submit' => true, 'events' => $events->toArray()])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->router->generate('admin_list_operator')
            ->shouldNotBeCalled()
        ;

        $exception = new EmailAlreadyExistsException();
        $this->commandBus
            ->handle($update)
            ->shouldBeCalled()
            ->willThrow($exception)
        ;

        $this->flashBag
            ->add('success', 'flash.admin.operator.update.success')
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
            ->render('AdminBundle:Operator:update.html.twig', ['form' => $view])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal(), new AdminDomain($this->admin->reveal()), $this->operator->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }
}
