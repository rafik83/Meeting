<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planner;

use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\MeetingSolutionListQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PlannerController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function indexAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $lastPlannerJob = $this
            ->get('vimeet_infrastructure.repository.planner_job_repository')
            ->findLastByEvent($event)
        ;

        $isEventOpened = $this->get('domain.key_dates.checker.event_open_access_checker')->allowedToAccess($event);

        $meetingSolutions = $this->get('tactician.commandbus.query')->handle(new MeetingSolutionListQuery($event));

        return $this->render('AdminBundle:Planner:index.html.twig', [
            'event'            => $event,
            'meetingSolutions' => $meetingSolutions,
            'lastPlannerJob'   => $lastPlannerJob,
            'isEventOpened'    => $isEventOpened,
        ]);
    }
}
