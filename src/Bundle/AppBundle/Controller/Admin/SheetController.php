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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\SheetListView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SheetController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $locale = $event->getAvailableLocale($request->getLocale());

        $sheets = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->paginate($request->query->getInt('page', 1), 20, $event, $locale);

        $sheets->results = array_map(function (Sheet $sheet) use ($locale) {
            return new SheetListView(
                $sheet->getId(),
                $this->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')->guessSheetInfo($sheet),
                $sheet->getType()->getTranslations()->get($locale)->getTitle()
            );
        }, $sheets->results);

        return $this->render('VimeetAppBundle:Admin/Sheet:list.html.twig', [
            'event'  => $event,
            'sheets' => $sheets,
        ]);
    }
}
