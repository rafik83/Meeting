<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Update;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\UpdateAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\UpdateType;
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
    private $commandBus;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    public function setUp()
    {
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
    }

    public function testInvokeAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $request = $this->prophesize(Request::class);
        $tip = $this->prophesize(Tip::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(false);

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->engine->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal()
        );

        $action($request->reveal(), $tip->reveal());
    }

    public function testInvoke()
    {
        $request = $this->prophesize(Request::class);
        $tip = $this->prophesize(Tip::class);
        $tip->getTranslations()->willReturn([]);
        $tip->getTitle()->willReturn('toto');
        $tip->isOnMeetingManagement()->willReturn(true);
        $tip->isOnPrintPlanning()->willReturn(false);
        $tip->isOnCatalog()->willReturn(false);
        $tip->isOnSheet()->willReturn(false);
        $tip->isOnAgenda()->willReturn(false);
        $tip->isOnPackage()->willReturn(false);
        $tip->isOnContacts()->willReturn(false);
        $tip->isOnProgram()->willReturn(false);
        $tip->isOnConfirmationPhone()->willReturn(false);
        $tip->isOnNetworking()->willReturn(false);
        $update = new Update($tip->reveal());
        $form = $this->prophesize(Form::class);

        $form->handleRequest($request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $formView = $this->prophesize(FormView::class);
        $form->createView()->willReturn($formView->reveal());

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->flashBag->add(Argument::any())->shouldNotBeCalled();

        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $this->formFactory
            ->create(UpdateType::class, $update, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->engine
            ->renderResponse(UpdateAction::TEMPLATE, ['form' => $formView->reveal()])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->engine->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal()
        );

        $result = $action($request->reveal(), $tip->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandle()
    {
        $request = $this->prophesize(Request::class);
        $tip = $this->prophesize(Tip::class);
        $tip->getTranslations()->willReturn([]);
        $tip->getTitle()->willReturn('toto');
        $tip->isOnMeetingManagement()->willReturn(true);
        $tip->isOnPrintPlanning()->willReturn(false);
        $tip->isOnCatalog()->willReturn(false);
        $tip->isOnSheet()->willReturn(false);
        $tip->isOnAgenda()->willReturn(false);
        $tip->isOnPackage()->willReturn(false);
        $tip->isOnContacts()->willReturn(false);
        $tip->isOnProgram()->willReturn(false);
        $tip->isOnConfirmationPhone()->willReturn(false);
        $tip->isOnNetworking()->willReturn(false);
        $update = new Update($tip->reveal());
        $form = $this->prophesize(Form::class);

        $form->handleRequest($request->reveal())->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $form->createView()->shouldNotBeCalled();

        $this->commandBus->handle($update)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.tip.update.success')->shouldBeCalled();

        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $this->formFactory
            ->create(UpdateType::class, $update, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->router->generate('admin_tip_list')->shouldBeCalled()->willReturn('route');
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $action = new UpdateAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->formFactory->reveal(),
            $this->flashBag->reveal(),
            $this->engine->reveal(),
            $this->commandBus->reveal(),
            $this->router->reveal()
        );

        $result = $action($request->reveal(), $tip->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('route', $result->getTargetUrl());
    }
}
