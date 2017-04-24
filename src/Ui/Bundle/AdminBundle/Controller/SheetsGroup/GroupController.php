<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\SheetsGroup;

use Proximum\Vimeet\Application\Command\Sheet\Group\Create;
use Proximum\Vimeet\Application\Exception\Group\NoSheetsAvailableForUserAndForEvent;
use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotFoundForGivenEmailException;
use Proximum\Vimeet\Application\Query\Group\Sheet\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Group\SearchUserQuery;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\GroupListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function listAction(Event $event)
    {
        // Only admin & organizers are allowed to manage sheetsGroup
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $groupViews = $this->get('tactician.commandbus')->handle(new GroupListViewQuery($event));

        return $this->render('AdminBundle:SheetsGroup:list.html.twig', [
            'event'      => $event,
            'groupViews' => $groupViews,
        ]);
    }

    /**
     * Search user by email to pre-populate the real create form
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return Response|RedirectResponse
     */
    public function preCreateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $searchUser = new SearchUserQuery($event);
        $form  = $this->createForm(SearchType::class, $searchUser, ['event' => $event]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $userView = $this->get('tactician.commandbus')->handle($searchUser);

                return $this->redirectToRoute('admin_sheets_group_create', [
                    'event' => $event->getId(),
                    'user'  => $userView->id,
                ]);
            } catch (UserNotAllowedToManageGroupException $exception) {
                $this->notifyFlashError($exception->email,  'flash.admin.group.create.error.user_not_allowed_to_manage');
            } catch (UserNotFoundForGivenEmailException $exception) {
                $this->notifyFlashError($exception->email, 'flash.admin.group.create.error.email_not_found');
            }
        }

        return $this->render('@Admin/SheetsGroup/pre_create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param User    $user
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request, Event $event, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sheetViews  = null;
        $querySheets = new SheetViewQuery($event, $user, $event->getAvailableLocale($request->getLocale()));

        try {
            $sheetViews = $this->get('tactician.commandbus')->handle($querySheets);
        } catch (NoSheetsAvailableForUserAndForEvent $exception) {
            $this->addFlash('error', 'flash.admin.group.create.error.no_sheet_available');

            return $this->redirectToRoute('admin_sheets_group_list', ['event' => $event->getId()]);
        }

        $command = new Create($event, $user, $sheetViews);
        $form    = $this->createForm(CreateType::class, $command, ['sheetViews' => $sheetViews]);

        try {
            if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
                $this->get('tactician.commandbus')->handle($command);
                $this->addFlash('success', 'flash.admin.group.create.success');

                return $this->redirectToRoute('admin_sheets_group_list', ['event' => $event->getId()]);
            }
        } catch (UserNotAllowedToManageGroupException $exception) {
            $this->notifyFlashError($exception->email,  'flash.admin.group.create.error.user_not_allowed_to_manage');
        }

        return $this->render('@Admin/SheetsGroup/create.html.twig', [
            'event' => $event,
            'user'  => $user,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param string $userEmail
     * @param string $translationKey
     */
    private function notifyFlashError($userEmail, $translationKey)
    {
        $translator = $this->get('translator');

        $this->addFlash(
            'error',
            $translator->trans(
                $translationKey,
                ['%email%' => $userEmail],
                'flashes'
            )
        );
    }
}
