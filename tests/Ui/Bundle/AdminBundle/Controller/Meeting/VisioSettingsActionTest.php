<?php


namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Visio\UpdateVisioSettings;
use Proximum\Vimeet\Application\Components\Visio\VisioSettingsRetriever;
use Proximum\Vimeet\Application\Query\Visio\UpdateVisioSettingsViewQuery;
use Proximum\Vimeet\Application\View\Visio\UpdateVisioSettingsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Meeting\VisioSettingsAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Meeting\Visio\SettingsType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class VisioSettingsActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $visioSettingsRetriever;

    /** @var ObjectProphecy */
    private $visioSettings;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->visioSettingsRetriever = $this->prophesize(VisioSettingsRetriever::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
        $this->event->getLocales()->willReturn(['fr']);
        $this->visioSettings = $this->prophesize(VisioSettings::class);
    }

    public function testAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new VisioSettingsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->visioSettingsRetriever->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->event->reveal()
        );
    }

    public function testRender(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->visioSettingsRetriever->get($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($this->visioSettings->reveal())
        ;

        $this->visioSettings->hasHeader('fr')->shouldBeCalled()->willReturn(true);
        $this->visioSettings->hasEndImage('fr')->shouldBeCalled()->willReturn(true);
        $this->visioSettings->hasEndSound('fr')->shouldBeCalled()->willReturn(true);
        $this->visioSettings->getEndMessage('fr')->shouldBeCalled()->willReturn('message');

        $view = $this->prophesize(UpdateVisioSettingsView::class);
        $this->queryBus
            ->handle(
                new UpdateVisioSettingsViewQuery(
                    $this->event->reveal(),
                    $this->visioSettings->reveal()
                )
            )
            ->shouldBeCalled()
            ->willReturn($view->reveal())
        ;

        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $update = new UpdateVisioSettings($this->event->reveal(), $this->visioSettings->reveal());
        $this->formFactory
            ->create(
                SettingsType::class,
                $update,
                ['submit' => true,]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->twig
            ->render(
                VisioSettingsAction::TEMPLATE,
                [
                    'event' => $this->event->reveal(),
                    'form' => $formView->reveal(),
                    'view' => $view->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new VisioSettingsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->visioSettingsRetriever->reveal()
        );

        $result = $action(
            $this->request->reveal(),
            $this->event->reveal()
        );

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->visioSettingsRetriever->get($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($this->visioSettings->reveal())
        ;

        $this->visioSettings->hasHeader('fr')->shouldBeCalled()->willReturn(true);
        $this->visioSettings->hasEndImage('fr')->shouldBeCalled()->willReturn(true);
        $this->visioSettings->hasEndSound('fr')->shouldBeCalled()->willReturn(true);
        $this->visioSettings->getEndMessage('fr')->shouldBeCalled()->willReturn('message');

        $this->queryBus
            ->handle(
                new UpdateVisioSettingsViewQuery(
                    $this->event->reveal(),
                    $this->visioSettings->reveal()
                )
            )
            ->shouldNotBeCalled()
        ;

        $form = $this->prophesize(Form::class);
        $update = new UpdateVisioSettings($this->event->reveal(), $this->visioSettings->reveal());
        $this->formFactory
            ->create(
                SettingsType::class,
                $update,
                ['submit' => true,]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->event->getId()->willReturn(12);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.meeting.visio.settings.update.success')->shouldBeCalled();
        $this->router->generate(
            'admin_meeting_visio_settings',
            ['event' => 12]
        )->shouldBeCalled()->willReturn('/route');

        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $action = new VisioSettingsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->visioSettingsRetriever->reveal()
        );

        /** @var RedirectResponse */
        $result = $action(
            $this->request->reveal(),
            $this->event->reveal()
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
