<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Type\TypeChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * Event home.
     *
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request, EventView $eventView)
    {
        $locale = $request->getLocale();

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $sheets = $this
                ->get('vimeet_infrastructure.repository.sheet_repository')
                ->getSheetViewsByUserAndEvent($this->getUser()->getId(), $eventView->id, $locale);
        } else {
            $sheets = [];
        }

        $form = $this->createForm(TypeChoiceType::class, null, [
            'locale'  => $locale,
            'eventId' => $eventView->id,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $typeView = $form->getData()['type'];

            if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
                return $this->redirectToRoute('event_participate', ['typeView' => $typeView->id]);
            }

            return $this->redirectToRoute('event_register', ['typeView' => $typeView->id]);
        }

        return $this->render('EventBundle:Home:index.html.twig', [
            'eventView' => $eventView,
            'sheets'    => $sheets,
            'form'      => $form->createView(),
        ]);
    }
}
