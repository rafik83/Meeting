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
        $affect            = new Affect($tipViews, $typeViews);

        $form = $this->createForm(AffectType::class, null, [
                'tipViews'  => $affect->tips,
                'typeViews' => $affect->types,
            ]);

         if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

         }

        return $this->render('@Admin/Tip/Event/affect.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }
}
