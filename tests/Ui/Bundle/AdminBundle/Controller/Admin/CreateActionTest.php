<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Admin\Create;
use Proximum\Vimeet\Application\Exception\Admin\EmailAlreadyExistsException;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Admin\CreateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\CreateType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class CreateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter,
        $formFactory,
        $commandBus,
        $flashBag,
        $router,
        $engine,
        $errorFactory,
        $request
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
    }

    public function testAuthorization(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $action($this->request->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->formFactory->create(CreateType::class, Argument::type(Create::class), ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->engine
            ->render('AdminBundle:Admin:create.html.twig', ['form' => $view, 'existingAdmin'=> null])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $form = $this->prophesize(Form::class);
        $this->formFactory->create(CreateType::class, Argument::type(Create::class), ['submit' => true])
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
            ->handle(Argument::type(Create::class))
            ->shouldBeCalled()
        ;

        $this->flashBag
            ->add('success', 'flash.admin.admin.create.success')
            ->shouldBeCalled()
        ;

        $this->engine
            ->render('AdminBundle:Admin:create.html.twig', Argument::any())
            ->shouldNotBeCalled()
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal());
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/admin', $result->getTargetUrl());
    }

    public function testHandleEmailAlreadyExist(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $this->formFactory->create(CreateType::class, Argument::type(Create::class), ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->router->generate('admin_list_admin')
            ->shouldNotBeCalled()
        ;

        $dateTime = new \DateTime();
        $admin = new Admin('test@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);
        $exception = new EmailAlreadyExistsException('utilisateur', $admin);

        $this->commandBus
            ->handle(Argument::type(Create::class))
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
            ->render('AdminBundle:Admin:create.html.twig', ['form' => $view, 'existingAdmin' => null])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }

    public function testDeletedUser(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->shouldBeCalled()->willReturn('fr');
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $this->formFactory->create(CreateType::class, Argument::type(Create::class), ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->router->generate('admin_list_admin')
            ->shouldNotBeCalled()
        ;

        $dateTime = new \DateTime();
        $admin = new Admin('test@test.com', '__salt__', null, 'fr', 'toto', 'tata', Admin::ROLE_ORGANIZER, $dateTime);
        $exception = new EmailAlreadyExistsException('utilisateur', $admin);
        $admin->setDeletedAt(\DateTime::createFromFormat('d/m/Y', '22/10/2019'));

        $this->commandBus
            ->handle(Argument::type(Create::class))
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
            ->render('AdminBundle:Admin:create.html.twig', ['form' => $view, 'existingAdmin' => $admin])
            ->shouldBeCalled()
            ->willReturn('<html></html>')
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->router->reveal(),
            $this->engine->reveal(),
            $this->errorFactory->reveal()
        );

        $result = $action($this->request->reveal());
        $this->assertEquals('<html></html>', $result->getContent());
    }

}
