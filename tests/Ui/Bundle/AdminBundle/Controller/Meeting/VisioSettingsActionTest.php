<?php


namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateVisioSettings;
use Proximum\Vimeet\Domain\Model\Event;
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
use Symfony\Component\Templating\EngineInterface;

class VisioSettingsActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $engine;

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

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
        $this->event->getLocales()->willReturn(['fr']);
    }

    public function testAccessDenied()
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
            $this->engine->reveal(),
            $this->router->reveal()
        );

        $action(
            $this->request->reveal(),
            $this->event->reveal()
        );
    }

    public function testRender()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $update = new UpdateVisioSettings($this->event->reveal());
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

        $this->engine
            ->render(
                VisioSettingsAction::TEMPLATE,
                [
                    'event' => $this->event->reveal(),
                    'form' => $formView->reveal(),
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
            $this->engine->reveal(),
            $this->router->reveal()
        );

        $result = $action(
            $this->request->reveal(),
            $this->event->reveal()
        );

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $form = $this->prophesize(Form::class);
        $update = new UpdateVisioSettings($this->event->reveal());
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

        $this->engine->render(Argument::any())->shouldNotBeCalled();

        $action = new VisioSettingsAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->commandBus->reveal(),
            $this->engine->reveal(),
            $this->router->reveal()
        );

        $result = $action(
            $this->request->reveal(),
            $this->event->reveal()
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
