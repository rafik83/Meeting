<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Exception\MultipleSheets\Request\NoResultException;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetListViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestController extends Controller
{
    const PAGINATE_REQUEST_LIMIT = 50;

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Group       $sheetGroup
     *
     * @return Response
     */
    public function listAction(Request $request, EventDomain $eventDomain, Group $sheetGroup)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $sheets = $this->get('vimeet_infrastructure.repository.sheet_repository')->getByGroup($sheetGroup);

        try {
            $sheetListView = $this->get('tactician.commandbus.query')->handle(
                new SheetListViewQuery(
                    $sheets,
                    $request->getLocale(),
                    $request->get('page', 1),
                    self::PAGINATE_REQUEST_LIMIT
                )
            );
        } catch (NoResultException $exception) {
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('EventBundle:Sheet/Group/Request:index.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'sheetGroup'    => $sheetGroup,
            'sheetListView' => $sheetListView,
        ]);
    }
}
