<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip;

use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Application\Exception\Tip\NoTipAvailableException;
use Proximum\Vimeet\Application\Query\Tip\Event\TipListViewQuery as EventTipListViewQuery;
use \Proximum\Vimeet\Application\Query\Tip\TipListViewQuery;
use Proximum\Vimeet\Application\Query\Tip\Event\TypeListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\AffectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TipEventController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $tipListViewQuery = new EventTipListViewQuery($event, $request->query->get('page', 1));

        $tipListView = $this->get('tactician.commandbus')->handle($tipListViewQuery);

        return $this->render('@Admin/Tip/Event/list.html.twig', [
            'event'       => $event,
            'tipListView' => $tipListView
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function affectAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $locale = $event->getAvailableLocale($request->getLocale());

        try {
            $tipListViewQuery = new TipListViewQuery($locale);
            $tipViews = $this->get('tactician.commandbus')->handle($tipListViewQuery);
        } catch (NoTipAvailableException $exception) {
            $this->addFlash('error', $this->get('translator')->trans($exception->getMessage(), [], 'flashes'));
        }

        $typeListViewQuery = new TypeListViewQuery($event, $locale);
        $typeViews         = $this->get('tactician.commandbus')->handle($typeListViewQuery);
        $affect            = new Affect();

        $form = $this->createForm(AffectType::class, $affect, [
                'tipViews'  => $tipViews,
                'typeViews' => $typeViews,
            ]);

         if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($affect);
            $this->addFlash('success', 'flash.admin.tip.affect.success');

            return $this->redirectToRoute('admin_tip_event_list', ['event' => $event]);
         }

        return $this->render('@Admin/Tip/Event/affect.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }
}
