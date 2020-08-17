<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Happening\Update;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateActionTest extends TestCase
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

    /** @var ObjectProphecy */
    private $admin;

    /** @var AdminDomain */
    private $adminDomain;

    /** @var ObjectProphecy */
    private $happening;

    /** @var ObjectProphecy */
    private $translator;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->adminDomain = new AdminDomain($this->admin->reveal());
        $this->event->getLocales()->willReturn(['fr']);
        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $this->happening = $this->prophesize(Happening::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
    }

    public function testAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->happening->reveal(), $this->adminDomain);
    }

    public function testAccessDeniedEventDifferent(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $event = $this->prophesize(Event::class);
        $this->happening->getEvent()->willReturn($event->reveal());
        $this->happening->getProducts()->willReturn([]);

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->happening->reveal(), $this->adminDomain);
    }

    public function testRender(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->happening->getEvent()->willReturn($this->event->reveal());
        $this->happening->getProducts()->willReturn([]);
        $category = $this->prophesize(Happening\Category::class);
        $this->happening->getCategory()->willReturn($category->reveal());
        $this->happening->getBegin()->willReturn(new \DateTime());
        $this->happening->getEnd()->willReturn(new \DateTime());
        $this->happening->isQuestionAllowed()->willReturn(false);
        $this->happening->getLimitParticipant()->willReturn(null);
        $this->event->getLocales()->willReturn([]);
        $this->happening->getSpeakers()->willReturn([]);
        $this->happening->getTypes()->willReturn([]);
        $this->happening->getInvitationCode()->shouldBeCalled()->willReturn('toto');
        $this->happening->isWebinar()->shouldBeCalled()->willReturn(false);
        $this->happening->isInteractiveWebinar()->shouldBeCalled()->willReturn(false);
        $this->happening->isVideoWebinar()->shouldBeCalled()->willReturn(false);
        $this->happening->getLiveUrl()->shouldBeCalled()->willReturn(null);
        $this->happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $this->happening->isSidebarAllowed()->willReturn(true);
        $this->happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);

        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());
        $update = new Update($this->happening->reveal());
        $this->formFactory
            ->create(
                UpdateType::class,
                $update,
                [
                    'admin'  => $this->admin->reveal(),
                    'event'  => $this->event->reveal(),
                    'locale' => 'fr',
                    'submit' => true,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->engine
            ->renderResponse(
                UpdateAction::TEMPLATE,
                [
                    'event' => $this->event->reveal(),
                    'form' => $formView->reveal(),
                    'products' => [],
                    'happening' => $this->happening->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->happening->reveal(), $this->adminDomain);

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle(): void
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->happening->getEvent()->willReturn($this->event->reveal());
        $this->happening->getProducts()->willReturn([]);
        $category = $this->prophesize(Happening\Category::class);
        $this->happening->getCategory()->willReturn($category->reveal());
        $this->happening->getBegin()->willReturn(new \DateTime());
        $this->happening->getEnd()->willReturn(new \DateTime());
        $this->happening->isQuestionAllowed()->willReturn(false);
        $this->happening->getLimitParticipant()->willReturn(null);
        $this->event->getLocales()->willReturn([]);
        $this->happening->getSpeakers()->willReturn([]);
        $this->happening->getTypes()->willReturn([]);
        $this->happening->getInvitationCode()->shouldBeCalled()->willReturn('toto');
        $this->happening->isWebinar()->shouldBeCalled()->willReturn(false);
        $this->happening->isInteractiveWebinar()->shouldBeCalled()->willReturn(false);
        $this->happening->isVideoWebinar()->shouldBeCalled()->willReturn(false);
        $this->happening->getLiveUrl()->shouldBeCalled()->willReturn(null);
        $this->happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $this->happening->isSidebarAllowed()->willReturn(true);
        $this->happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);

        $form = $this->prophesize(Form::class);
        $update = new Update($this->happening->reveal());
        $this->formFactory
            ->create(
                UpdateType::class,
                $update,
                [
                    'admin'  => $this->admin->reveal(),
                    'event'  => $this->event->reveal(),
                    'locale' => 'fr',
                    'submit' => true,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->event->getId()->willReturn(12);
        $this->happening->getId()->willReturn(14);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.happening.update.success')->shouldBeCalled();
        $this->router->generate(
            'admin_happening_update',
            ['event' => 12, 'happening' => 14]
        )->shouldBeCalled()->willReturn('/route');

        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->engine->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->happening->reveal(), $this->adminDomain);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
