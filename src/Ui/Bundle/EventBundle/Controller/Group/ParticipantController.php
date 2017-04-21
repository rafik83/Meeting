<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Command\Group\Participant\UpdateUsersSheets;
use Proximum\Vimeet\Application\Query\Sheet\Group\GroupViewQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\UsersParticipantViewQuery;
use Proximum\Vimeet\Application\View\Group\Participant\UserParticipantView;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Group\UsersSheetsType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParticipantController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Group       $sheetGroup
     *
     * @return Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain, Group $sheetGroup)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $event = $eventDomain->getEvent();

        $groupView = $this->get('tactician.commandbus.query')->handle(
            new GroupViewQuery($sheetGroup)
        );

        $userParticipantViews = $this->get('tactician.commandbus.query')->handle(
            new UsersParticipantViewQuery($sheetGroup)
        );

        $updateUsersSheets = new UpdateUsersSheets($sheetGroup, $userParticipantViews);

        $form = $this->createForm(
            UsersSheetsType::class,
            $updateUsersSheets,
            [
                'group' => $sheetGroup,
                'updateUsersSheets' => $updateUsersSheets,
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.group.participant.update_users_sheets_handler')->handle($updateUsersSheets);

            $this->addFlash('success', 'flash.group.participant.update_success');

            return $this->redirectToRoute(
                'event_sheet_group_participant_index',
                ['sheetGroup' => $sheetGroup->getId()]
            );
        }

        return $this->render('EventBundle:Sheet/Group/Participant:index.html.twig', [
            'event'                => $event,
            'groupView'            => $groupView,
            'userParticipantViews' => $userParticipantViews,
            'form'                 => $form->createView(),
        ]);
    }
}
