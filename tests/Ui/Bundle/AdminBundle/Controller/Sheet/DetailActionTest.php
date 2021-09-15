<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\AddComment;
use Proximum\Vimeet\Application\Query\Sheet\Detail\SheetDetailQuery;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetDetailsView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet\DetailAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\CommentType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Twig\Environment;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class DetailActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationChecker;

    /** @var ObjectProphecy */
    private $typeRepository;

    /** @var ObjectProphecy */
    private $invoiceRepository;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $router;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $admin;

    /** @var AdminDomain */
    private $adminDomain;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $type;

    public function setUp()
    {
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $this->invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->event = $this->prophesize(Event::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->adminDomain = new AdminDomain($this->admin->reveal());
        $this->request = $this->prophesize(Request::class);
        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->user = $this->prophesize(User::class);
        $this->sheet->getOwner()->willReturn($this->user->reveal());
        $this->type = $this->prophesize(Type::class);
        $this->type->getTitle('fr')->willReturn('type');
        $this->sheet->getType()->willReturn($this->type->reveal());
    }

    public function testRender()
    {
        $this->authorizationChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationChecker
            ->isGranted('PERMISSION_SHEET_ACCESS', $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $sheetDetailView = $this->prophesize(SheetDetailsView::class);
        $this->queryBus
            ->handle(new SheetDetailQuery($this->admin->reveal(), $this->sheet->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetDetailView->reveal())
        ;

        $this->sheet->getCommercialStatus()->willReturn(CommercialStatus::STATUS_NONE);
        $this->sheet->getReminderDate()->willReturn(new \DateTime());

        $this->typeRepository->countByEvent($this->event->reveal())->shouldBeCalled()->willReturn(4);
        $this->invoiceRepository->isSheetInvoiced($this->sheet->reveal())->shouldBeCalled()->willReturn(null);
        $this->meetingRepository->countMeetingsOfSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(4);

        $addComment = new AddComment($this->sheet->reveal(), $this->admin->reveal());
        $form = $this->prophesize(Form::class);
        $formView = $this->prophesize(FormView::class);
        $this->formFactory
            ->create(CommentType::class, $addComment, [
                'submit' => true,
            ])->shouldBeCalled()
            ->willReturn($form->reveal())
        ;
        $form->createView()->willReturn($formView->reveal());

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->twig
            ->render(DetailAction::TEMPLATE, [
                'event' => $this->event->reveal(),
                'sheet' => $this->sheet->reveal(),
                'sheetTypeTitle' => 'type',
                'details' => $sheetDetailView->reveal(),
                'addCommentForm' => $formView->reveal(),
                'changeTypeForm' => null,
            ])->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new DetailAction(
            $this->authorizationChecker->reveal(),
            $this->typeRepository->reveal(),
            $this->invoiceRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->flashBag->reveal(),
            $this->twig->reveal(),
            $this->router->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal(), $this->sheet->reveal(), $this->adminDomain);

        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHandleAddComment()
    {
        $this->authorizationChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationChecker
            ->isGranted('PERMISSION_SHEET_ACCESS', $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $sheetDetailView = $this->prophesize(SheetDetailsView::class);
        $this->queryBus
            ->handle(new SheetDetailQuery($this->admin->reveal(), $this->sheet->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetDetailView->reveal())
        ;

        $this->sheet->getCommercialStatus()->willReturn(CommercialStatus::STATUS_NONE);
        $this->sheet->getReminderDate()->willReturn(new \DateTime());

        $this->typeRepository->countByEvent($this->event->reveal())->shouldBeCalled()->willReturn(4);
        $this->invoiceRepository->isSheetInvoiced($this->sheet->reveal())->shouldBeCalled()->willReturn(null);
        $this->meetingRepository->countMeetingsOfSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(4);

        $addComment = new AddComment($this->sheet->reveal(), $this->admin->reveal());
        $form = $this->prophesize(Form::class);
        $this->formFactory
            ->create(CommentType::class, $addComment, [
                'submit' => true,
            ])->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form->reveal());
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->commandBus->handle($addComment)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.sheet.add_comment.success')->shouldBeCalled();
        $this->event->getId()->willReturn(14);
        $this->sheet->getId()->willReturn(12);

        $this->router
            ->generate('admin_sheet_details', [
                'event' => 14,
                'sheet' => 12,
            ])
            ->shouldBeCalled()
            ->willReturn('/route')
            ;

        $action = new DetailAction(
            $this->authorizationChecker->reveal(),
            $this->typeRepository->reveal(),
            $this->invoiceRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->queryBus->reveal(),
            $this->flashBag->reveal(),
            $this->twig->reveal(),
            $this->router->reveal()
        );

        /** @var RedirectResponse */
        $result = $action($this->request->reveal(), $this->event->reveal(), $this->sheet->reveal(), $this->adminDomain);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route', $result->getTargetUrl());
    }
}
