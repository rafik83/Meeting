<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MultipleSheets;

use Proximum\Vimeet\Application\Exception\MultipleSheets\Request\NoResultException;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetListViewQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class RequestController extends Controller
{
    const PAGINATE_REQUEST_LIMIT = 50;

    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param UserInterface $user
     *
     * @return Response
     */
    public function listAction(Request $request, EventDomain $eventDomain, UserInterface $user)
    {
        $event = $eventDomain->getEvent();

        try {
            $sheets = $this
                ->get('vimeet_infrastructure.repository.sheet_repository')
                ->getSheetsByUserAndEvent($user, $event);

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

        return $this->render('EventBundle:MultipleSheets/Request:index.html.twig', [
            'event'         => $event,
            'sheetListView' => $sheetListView
        ]);
    }
}
