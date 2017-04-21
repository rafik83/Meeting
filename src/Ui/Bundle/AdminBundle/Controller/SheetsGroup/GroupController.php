<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\SheetsGroup;

use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\GroupListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
}
