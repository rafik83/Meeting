<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Update;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\UpdateType;
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
    private $commandBus;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $admin;

    /** @var ObjectProphecy */
    private $tip;

    public function setUp(): void
    {
        $this->admin = $this->prophesize(Admin::class);
        $this->tip = $this->prophesize(Tip::class);
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);

        $this->tip->getTitle()->willReturn('title');
        $this->tip->isOnMeetingManagement()->willReturn(true);
        $this->tip->isOnCatalog()->willReturn(true);
        $this->tip->isOnPrintPlanning()->willReturn(true);
        $this->tip->isOnSheet()->willReturn(true);
        $this->tip->isOnProgram()->willReturn(true);
        $this->tip->isOnAgenda()->willReturn(true);
        $this->tip->isOnPackage()->willReturn(true);
        $this->tip->isOnContacts()->willReturn(true);
        $this->tip->isOnConfirmationPhone()->willReturn(true);
        $this->tip->isOnNetworking()->willReturn(false);
        $this->tip->getTypes()->willReturn([]);
        $this->tip->getTranslation('fr')->willReturn(null);
        $this->tip->getTranslation('en')->willReturn(null);
    }

    public function testAccessDeniedRole(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->formFactory->create(Argument::any())->shouldNotBeCalled();
        $this->twig->render(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $action = new UpdateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->tip->reveal(), $this->admin->reveal());
    }

    public function testAccessDeniedPermissionEvent()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->formFactory->create(Argument::any())->shouldNotBeCalled();
        $this->twig->render(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $action = new UpdateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->tip->reveal(), $this->admin->reveal());
    }

    public function testAccessDeniedEventDifferent(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $event = $this->prophesize(Event::class);
        $this->tip->getEvent()->willReturn($event->reveal());

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->formFactory->create(Argument::any())->shouldNotBeCalled();
        $this->twig->render(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $action = new UpdateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->tip->reveal(), $this->admin->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->tip->getEvent()->willReturn($this->event->reveal());
        $this->tip->getDisplay()->shouldBeCalled()->willReturn(Tip::DISPLAY_DEFAULT);
        $this->tip->getConditionOnOrders()->shouldBeCalled()->willReturn([]);
        $this->tip->hasConditionCart()->shouldBeCalled()->willReturn(true);
        $this->tip->hasConditionPendingMeetingProposition()->shouldBeCalled()->willReturn(null);
        $this->tip->hasConditionRemainingToPay()->shouldBeCalled()->willReturn(false);
        $this->tip->hasConditionCompleteSheet()->shouldBeCalled()->willReturn(true);
        $this->tip->hasConditionPhoneConfirmed()->shouldBeCalled()->willReturn(null);

        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->request->getLocale()->willReturn('fr');
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $update = new Update($this->tip->reveal());
        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $form->createView()->shouldBeCalled()->willReturn($formView->reveal());

        $this->commandBus->handle($update)->shouldNotBeCalled();
        $this->flashBag->add('success', 'flash.admin.tip.event.update.success')->shouldNotBeCalled();

        $this->formFactory
            ->create(
                UpdateType::class,
                $update,
                [
                    'submit' => true,
                    'admin' => $this->admin->reveal(),
                    'event' => $this->event->reveal(),
                    'locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $response = new Response('Update Form');
        $this->twig->render(
            UpdateAction::TEMPLATE,
            [
                'event' => $this->event->reveal(),
                'form' => $formView->reveal(),
            ]
        )->shouldBeCalled()
            ->willReturn('Update Form');

        $action = new UpdateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->tip->reveal(), $this->admin->reveal());

        $this->assertEquals($response, $result);
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->tip->getEvent()->willReturn($this->event->reveal());
        $this->tip->getDisplay()->shouldBeCalled()->willReturn(Tip::DISPLAY_ALWAYS_OPENED);
        $this->tip->getConditionOnOrders()->shouldBeCalled()->willReturn([]);
        $this->tip->hasConditionCart()->shouldBeCalled()->willReturn(true);
        $this->tip->hasConditionPendingMeetingProposition()->shouldBeCalled()->willReturn(null);
        $this->tip->hasConditionRemainingToPay()->shouldBeCalled()->willReturn(false);
        $this->tip->hasConditionCompleteSheet()->shouldBeCalled()->willReturn(true);
        $this->tip->hasConditionPhoneConfirmed()->shouldBeCalled()->willReturn(null);

        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $this->request->getLocale()->willReturn('fr');
        $update = new Update($this->tip->reveal());
        $this->event->getId()->willReturn(12);
        $form = $this->prophesize(Form::class);

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $form->createView()->shouldNotBeCalled();

        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.tip.event.update.success')->shouldBeCalled();

        $this->formFactory
            ->create(
                UpdateType::class,
                $update,
                [
                    'submit' => true,
                    'admin' => $this->admin->reveal(),
                    'event' => $this->event->reveal(),
                    'locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->twig->render(Argument::any())->shouldNotBeCalled();
        $this->router->generate('admin_tip_event_list', ['event' => 12])->shouldBeCalled()->willReturn('route');

        $action = new UpdateAction(
            $this->commandBus->reveal(),
            $this->router->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->tip->reveal(), $this->admin->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('route', $result->getTargetUrl());
    }
}
