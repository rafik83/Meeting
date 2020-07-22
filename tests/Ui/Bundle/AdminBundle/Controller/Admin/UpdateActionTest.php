<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Admin\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Admin\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\UpdateType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class UpdateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter,
        $formFactory,
        $commandBus,
        $flashBag,
        $router,
        $engine,
        $errorFactory,
        $request,
        $admin
    ;
    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->errorFactory = $this->prophesize(ErrorFactory::class);
        $this->request = $this->prophesize(Request::class);
        $this->admin = $this->prophesize(Admin::class);
    }

    public function testAuthorization(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $action($this->request->reveal(), $this->admin->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->admin->getEmail()->willReturn('test@example.net');
        $this->admin->getLastname()->willReturn('Test');
        $this->admin->getFirstname()->willReturn('Test');
        $this->admin->getRole()->willReturn('ROLE_ORGANIZER');
        $this->admin->getEvents()->willReturn(new ArrayCollection());

        $update = new Update($this->admin->reveal());
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $this->formFactory->create(UpdateType::class, $update, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->engine
            ->render('AdminBundle:Admin:update.html.twig', ['form' => $view])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal(), $this->admin->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->admin->getEmail()->willReturn('test@example.net');
        $this->admin->getLastname()->willReturn('Test');
        $this->admin->getFirstname()->willReturn('Test');
        $this->admin->getRole()->willReturn('ROLE_ORGANIZER');
        $this->admin->getEvents()->willReturn(new ArrayCollection());

        $update = new Update($this->admin->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory->create(UpdateType::class, $update, ['submit' => true])
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
            ->handle($update)
            ->shouldBeCalled()
        ;

        $this->flashBag
            ->add('success', 'flash.admin.admin.update.success')
            ->shouldBeCalled()
        ;

        $this->engine
            ->render('AdminBundle:Admin:create.html.twig', Argument::any())
            ->shouldNotBeCalled()
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal(), $this->admin->reveal());
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/admin', $result->getTargetUrl());
    }

    public function testHandleException(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->admin->getEmail()->willReturn('test@example.net');
        $this->admin->getLastname()->willReturn('Test');
        $this->admin->getFirstname()->willReturn('Test');
        $this->admin->getRole()->willReturn('ROLE_ORGANIZER');
        $this->admin->getEvents()->willReturn(new ArrayCollection());

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $update = new Update($this->admin->reveal());
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $this->formFactory->create(UpdateType::class, $update, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->router
            ->generate('admin_list_admin')
            ->shouldNotBeCalled()
        ;

        $exception = new EmailAlreadyExistsException();
        $this->commandBus
            ->handle($update)
            ->shouldBeCalled()
            ->willThrow($exception)
        ;

        $this->flashBag
            ->add('success', 'flash.admin.admin.create.success')
            ->shouldNotBeCalled()
        ;

        $error = new FormError('error');
        $this->errorFactory->create('validators.emailAlreadyExist', 'fr')
            ->shouldBeCalled()
            ->willReturn($error)
        ;

        $form->get('email')->shouldBeCalled()->willReturn($form);
        $form->addError($error)->shouldBeCalled();

        $this->engine
            ->render('AdminBundle:Admin:update.html.twig', ['form' => $view])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal(), $this->admin->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }
}
