<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Sheet\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Participant\Import\CreateMapping;
use Proximum\Vimeet\Application\Query\Participant\Import\ImportResultViewQueryHandler;
use Proximum\Vimeet\Domain\Exception\Sheet\ImportMapping\TitleNotUniqueException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\ParticipantImport;
use Proximum\Vimeet\Domain\View\Normalizer\ParticipantDenormalizerView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet\Import\ResultAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\SheetImportMapping\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\SheetImportMapping\SaveType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Symfony\Component\Translation\TranslatorInterface;

class ResultActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter,
        $importResultViewQueryHandler,
        $twig,
        $formFactory,
        $commandBus,
        $router,
        $flashBag,
        $translator,
        $event,
        $request
    ;

    public function setUp(): void
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->importResultViewQueryHandler = $this->prophesize(ImportResultViewQueryHandler::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->request = $this->prophesize(Request::class);
    }

    public function testInvokePermission(): void
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->importResultViewQueryHandler->handle()->shouldNotBeCalled();
        $this->formFactory->create(Argument::any())->shouldNotBeCalled();
        $this->router->generate(Argument::any())->shouldNotBeCalled();
        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $action = new ResultAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->importResultViewQueryHandler->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );
        $action($this->request->reveal(), $this->event->reveal());
    }

    public function testInvoke(): void
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $participantImport = $this->prophesize(ParticipantImport::class);
        $participantImport->hasDiffInMapping()->shouldBeCalled()->willReturn(true);
        $participantImport->hasImportMapping()->shouldBeCalled()->willReturn(false);
        $participantImport->getMapping()->shouldBeCalled()->willReturn(['mapping' => 'mapping']);
        $participantDenormalizerView = new ParticipantDenormalizerView(
            $participantImport->reveal(),
            0,
            0,
            0,
            0,
            []
        );
        $this->importResultViewQueryHandler->handle()->shouldBeCalled()->willReturn($participantDenormalizerView);

        $create = new CreateMapping($this->event->reveal(), ['mapping' => 'mapping']);
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);
        $this->formFactory->create(CreateType::class, $create, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->formFactory->create(SaveType::class, Argument::any())
            ->shouldNotBeCalled()
        ;
        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->twig->render('AdminBundle:Sheet:importResult.html.twig', [
            'event' => $this->event->reveal(),
            'view' => $participantDenormalizerView,
            'createForm' => $view->reveal(),
            'updateForm' => null,
            'existingImportMapping' => false,
        ])->shouldBeCalled()
            ->willReturn('<html></html>');

        $action = new ResultAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->importResultViewQueryHandler->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );
        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertEquals('<html></html>', $result->getContent());
    }

    public function testInvokeHandleNotUniqueTitle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $participantImport = $this->prophesize(ParticipantImport::class);
        $participantImport->hasDiffInMapping()->shouldBeCalled()->willReturn(true);
        $participantImport->hasImportMapping()->shouldBeCalled()->willReturn(false);
        $participantImport->getMapping()->shouldBeCalled()->willReturn(['mapping' => 'mapping']);
        $participantDenormalizerView = new ParticipantDenormalizerView(
            $participantImport->reveal(),
            0,
            0,
            0,
            0,
            []
        );
        $this->importResultViewQueryHandler->handle()->shouldBeCalled()->willReturn($participantDenormalizerView);

        $create = new CreateMapping($this->event->reveal(), ['mapping' => 'mapping']);
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $this->formFactory->create(CreateType::class, $create, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->formFactory->create(SaveType::class, Argument::any())
            ->shouldNotBeCalled()
        ;
        $exception = new TitleNotUniqueException('title');
        $this->commandBus->handle($create)->shouldBeCalled()->willThrow($exception);
        $form->get('title')->shouldBeCalled()->willReturn($form->reveal());
        $this->translator
            ->trans(
                'validators.admin.sheet.import_mapping.title.not_unique',
                [],
                'validators'
            )->shouldBeCalled()
            ->willReturn('error');
        $form->addError(new FormError('error'))->shouldBeCalled();

        $this->twig->render('AdminBundle:Sheet:importResult.html.twig', [
            'event' => $this->event->reveal(),
            'view' => $participantDenormalizerView,
            'createForm' => $view->reveal(),
            'updateForm' => null,
            'existingImportMapping' => false,
        ])->shouldBeCalled()
            ->willReturn('<html></html>');

        $action = new ResultAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->importResultViewQueryHandler->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );
        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertEquals('<html></html>', $result->getContent());
    }

    public function testInvokeHandle(): void
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $participantImport = $this->prophesize(ParticipantImport::class);
        $participantImport->hasDiffInMapping()->shouldBeCalled()->willReturn(true);
        $participantImport->hasImportMapping()->shouldNotBeCalled();
        $participantImport->getMapping()->shouldBeCalled()->willReturn(['mapping' => 'mapping']);
        $participantDenormalizerView = new ParticipantDenormalizerView(
            $participantImport->reveal(),
            0,
            0,
            0,
            0,
            []
        );
        $this->importResultViewQueryHandler->handle()->shouldBeCalled()->willReturn($participantDenormalizerView);

        $create = new CreateMapping($this->event->reveal(), ['mapping' => 'mapping']);
        $form = $this->prophesize(Form::class);
        $form->createView()->shouldNotBeCalled();
        $form->handleRequest($this->request->reveal())->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $this->formFactory->create(CreateType::class, $create, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->formFactory->create(SaveType::class, Argument::any())
            ->shouldNotBeCalled()
        ;
        $this->commandBus->handle($create)->shouldBeCalled();
        $this->flashBag
            ->add('success', 'flash.admin.event.sheet.import_mapping.create.success')
            ->shouldBeCalled()
        ;
        $this->event->getId()->shouldBeCalled()->willReturn(12);
        $this->router
            ->generate('admin_sheet', [
                'event' => 12,
            ])
            ->shouldBeCalled()
            ->willReturn('/route/to/sheet_list')
        ;

        $this->twig->render('AdminBundle:Sheet:importResult.html.twig', Argument::any())->shouldNotBeCalled();

        $action = new ResultAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->importResultViewQueryHandler->reveal(),
            $this->formFactory->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->translator->reveal()
        );
        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/route/to/sheet_list', $result->getTargetUrl());
    }
}
