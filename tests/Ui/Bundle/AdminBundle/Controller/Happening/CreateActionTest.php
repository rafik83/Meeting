<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Happening\Create;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\CreateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class CreateActionTest extends TestCase
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
    private $admin;

    /** @var AdminDomain */
    private $adminDomain;

    /** @var ObjectProphecy */
    private $translator;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->twig = $this->prophesize(Environment::class);
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
        $this->translator = $this->prophesize(TranslatorInterface::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal(), $this->adminDomain);
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
        $allowHlsFormField = $this->prophesize(Form::class);
        $allowHlsFormField->getData()->willReturn(false);
        $form->get('allowHls')->willReturn($allowHlsFormField->reveal());

        $create = new Create($this->event->reveal());
        $this->formFactory
            ->create(
                CreateType::class,
                $create,
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

        $this->twig
            ->render(CreateAction::TEMPLATE, ['event' => $this->event->reveal(), 'form' => $formView->reveal()])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->adminDomain);

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
        $create = new Create($this->event->reveal());
        $this->formFactory
            ->create(
                CreateType::class,
                $create,
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
        $allowHlsFormField = $this->prophesize(Form::class);
        $allowHlsFormField->getData()->willReturn(false);
        $form->get('allowHls')->willReturn($allowHlsFormField->reveal());
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->commandBus->handle($create)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.happening.create.success')->shouldBeCalled();
        $this->router->generate('admin_happening_list', ['event' => 12])->shouldBeCalled()->willReturn('/route');

        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $action = new CreateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->adminDomain);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
