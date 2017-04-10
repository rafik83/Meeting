<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     * @param Group       $group
     *
     * @return Response
     */
    public function indexAction(EventDomain $eventDomain, Group $group)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $group);

        $event = $eventDomain->getEvent();

        return $this->render('EventBundle:Sheet/Group:index.html.twig', [
            'event' => $event,
            'group' => $group,
        ]);
    }
}
