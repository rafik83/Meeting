<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Duplicate;
use Proximum\Vimeet\Application\Command\Template\Registration\DuplicateResult;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate\DuplicateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Registration\DuplicateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class DuplicateActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $template;

    /** @var ObjectProphecy */
    private $admin;

    /** @var AdminDomain */
    private $adminDomain;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->template = $this->prophesize(RegistrationTemplate::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->adminDomain = new AdminDomain($this->admin->reveal());
    }

    public function testInvokeWithoutRole(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);

        $controller = new DuplicateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal()
        );

        $request = new Request();
        $controller($request, $this->template->reveal(), $this->adminDomain);
    }

    public function testInvokeWithoutPermissionToEditTemplate(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->authorizationCheckerAdapter
            ->isGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $this->template->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $controller = new DuplicateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal()
        );

        $request = new Request();
        $controller($request, $this->template->reveal(), $this->adminDomain);
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->authorizationCheckerAdapter
            ->isGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $this->template->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $request = new Request();
        $event = $this->prophesize(Event::class);
        $this->template->getEvent()->willReturn($event->reveal());
        $duplicate = new Duplicate($this->template->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(
                DuplicateType::class,
                $duplicate,
                [
                    'admin' => $this->admin->reveal(),
                    'submit' => true,
                ]
            )->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view->reveal());

        $form->handleRequest($request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $response = new Response('Duplicate form');
        $this->twig
            ->render(
                'AdminBundle:RegistrationTemplate:duplicate.html.twig', [
                    'template' => $this->template->reveal(),
                    'form' => $view->reveal()
                ]
            )->shouldBeCalled()
            ->willReturn('Duplicate form')
        ;

        $controller = new DuplicateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal()
        );

        $result = $controller($request, $this->template->reveal(), $this->adminDomain);

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testInvokeHandle(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->authorizationCheckerAdapter
            ->isGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $this->template->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $request = new Request();
        $event = $this->prophesize(Event::class);
        $this->template->getEvent()->willReturn($event->reveal());
        $duplicate = new Duplicate($this->template->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(
                DuplicateType::class,
                $duplicate,
                [
                    'admin' => $this->admin->reveal(),
                    'submit' => true,
                ]
            )->shouldBeCalled()
            ->willReturn($form->reveal())
        ;


        $form->handleRequest($request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);


        $duplicateTemplate = $this->prophesize(RegistrationTemplate::class);
        $duplicateResult = new DuplicateResult($duplicateTemplate->reveal());
        $duplicateTemplate->getId()->willReturn(12);
        $duplicateTemplate->getEvent()->willReturn($event->reveal());
        $duplicateTemplate->getFallback()->willReturn('fr');
        $this->commandBus->handle($duplicate)->shouldBeCalled()->willReturn($duplicateResult);

        $this->router
            ->generate('admin_template_registration_build', [
                'locale' => 'fr',
                'registrationTemplate' => 12,
            ])
            ->shouldBeCalled()
            ->willReturn('/path/to/route')
        ;

        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $controller = new DuplicateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal()
        );

        $result = $controller($request, $this->template->reveal(), $this->adminDomain);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
