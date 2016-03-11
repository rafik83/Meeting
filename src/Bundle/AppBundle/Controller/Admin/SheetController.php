<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SheetController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sheets = $this
            ->get('query.sheet.sheet_list_view_factory')
            ->paginate($event, $request->query->getInt('page', 1), 20, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Sheet:list.html.twig', [
            'event'  => $event,
            'sheets' => $sheets,
        ]);
    }
}
