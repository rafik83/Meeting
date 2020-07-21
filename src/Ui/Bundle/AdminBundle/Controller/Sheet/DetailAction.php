<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\AddComment;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType;
use Proximum\Vimeet\Application\Query\Sheet\Detail\SheetDetailQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\ChangeTypeType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\CommentType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DetailAction
{
    const TEMPLATE = 'AdminBundle:Sheet:details.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        TypeRepositoryInterface $typeRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        MeetingRepositoryInterface $meetingRepository,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        FlashBagInterface $flashBag,
        EngineInterface $engine,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->typeRepository = $typeRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->meetingRepository = $meetingRepository;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->flashBag = $flashBag;
        $this->engine = $engine;
        $this->router = $router;
    }

    /**
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request, Event $event, Sheet $sheet, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationChecker->isGranted('PERMISSION_SHEET_ACCESS', $sheet)) {
            throw new AccessDeniedException();
        }

        if ($sheet->getEvent() !== $event) {
            throw new NotFoundHttpException(
                sprintf(
                    'The sheet %s is not on this event %s',
                    $sheet->getId(),
                    $event->getId()
                )
            );
        }

        $admin = $adminDomain->getAdmin();
        $locale = $event->getAvailableLocale($request->getLocale());

        $sheetDetailView = $this->queryBus->handle(new SheetDetailQuery($admin, $sheet, $locale));

        $changeTypeForm = null;

        if ($this->typeRepository->countByEvent($event) > 1
            && null === $this->invoiceRepository->isSheetInvoiced($sheet)
            && 0 === $this->meetingRepository->countMeetingsOfSheet($sheet)
        ) {
            $changeType = new ChangeType($sheet, $sheet->getType(), $admin, $locale);

            $changeTypeForm = $this->formFactory->create(ChangeTypeType::class, $changeType, [
                'event' => $event,
                'type' => $sheet->getType(),
                'locale' => $locale,
                'submit' => true,
            ]);

            if ($changeTypeForm->handleRequest($request)->isSubmitted() && $changeTypeForm->isValid()) {
                $this->commandBus->handle($changeType);
                $this->flashBag->add('success', 'flash.admin.sheet.change_type.success');

                return new RedirectResponse($this->router->generate('admin_sheet_details', [
                    'event' => $event->getId(),
                    'sheet' => $sheet->getId(),
                ]));
            }
        }

        $addComment = new AddComment($sheet, $admin);
        $addCommentForm = $this->formFactory->create(CommentType::class, $addComment, [
            'submit' => true,
        ]);

        if ($addCommentForm->handleRequest($request)->isSubmitted() && $addCommentForm->isValid()) {
            $this->commandBus->handle($addComment);
            $this->flashBag->add('success', 'flash.admin.sheet.add_comment.success');

            return new RedirectResponse($this->router->generate('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ]));
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event' => $event,
            'sheet' => $sheet,
            'sheetTypeTitle' => $sheet->getType()->getTitle($locale),
            'details' => $sheetDetailView,
            'addCommentForm' => $addCommentForm->createView(),
            'changeTypeForm' => null === $changeTypeForm ? null : $changeTypeForm->createView(),
        ]);
    }
}
