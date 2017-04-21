<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\SheetsGroup;

use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotFoundForGivenEmailException;
use Proximum\Vimeet\Application\Query\Group\UserViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\GroupListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $sheetsGroupRepository = $this->get('repository.sheet.group_repository');
        $sheetsGroups = $sheetsGroupRepository->getAllByEventOrderedByTitle($event);

        $groupViews = $this->get('tactician.commandbus')->handle(new GroupListViewQuery($sheetsGroups));

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
     * @return Response
     */
    public function preCreateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $query = new UserViewQuery($event);
        $form  = $this->createForm(SearchType::class, $query, ['event' => $event]);
        $translator = $this->get('translator');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $user = $this->get('tactician.commandbus')->handle($query);

            } catch (UserNotAllowedToManageGroupException $exception) {
                $this->addFlash(
                    'error',
                    $translator->trans(
                        'flash.admin.group.create.error.user_not_allowed_to_manage',
                        ['%userName%' => $exception->userName],
                        'flashes'
                    )
                );
            } catch (UserNotFoundForGivenEmailException $exception) {
                $this->addFlash(
                    'error',
                    $translator->trans(
                        'flash.admin.group.create.error.email_not_found',
                        ['%email%' => $exception->email],
                        'flashes'
                    )
                );
            }
        }

        return $this->render('@Admin/SheetsGroup/pre_create.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }
}
