<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Create;
use Proximum\Vimeet\Application\Command\Template\Registration\CreateResult;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate\CreateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Registration\CreateType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class CreateActionTest extends TestCase
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
    private $event;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->template = $this->prophesize(RegistrationTemplate::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testInvokeWithoutRole(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);

        $controller = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $request = new Request();
        $controller($request, $this->event->reveal());
    }

    public function testInvokeWithoutPermissionOfEvent(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $controller = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $request = new Request();
        $controller($request, $this->event->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view->reveal());

        $command = new Create($this->event->reveal());
        $this->formFactory
            ->create(CreateType::class, $command, [
                'submit' => true
            ])->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $request = new Request();
        $form->handleRequest($request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->twig
            ->render('AdminBundle:RegistrationTemplate:create.html.twig', [
                'form' => $view->reveal()
            ])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $controller = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $request = new Request();
        $result = $controller($request, $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testInvokeHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $form = $this->prophesize(Form::class);

        $command = new Create($this->event->reveal());
        $this->formFactory
            ->create(CreateType::class, $command, [
                'submit' => true
            ])->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $request = new Request();
        $form->handleRequest($request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->twig
            ->render(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->template->getId()->shouldBeCalled()->willReturn(2);
        $createResult = new CreateResult($this->template->reveal());
        $this->commandBus->handle($command)->shouldBeCalled()->willReturn($createResult);

        $this->event->getId()->willReturn(12);
        $this->event->getLocaleFallback()->willReturn('fr');
        $this->router->generate('admin_template_registration_build', [
                'event' => 12,
                'registrationTemplate' => 2,
                'locale' => 'fr',
            ])
            ->shouldBeCalled()
            ->willReturn('/path/to/route')
        ;

        $controller = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->twig->reveal()
        );

        $request = new Request();
        $result = $controller($request, $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
