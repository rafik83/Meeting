<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\SearchFacet\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\SearchFacet;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\SearchFacet\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchFacetController extends Controller
{
    /**
     * @param Request     $request
     * @param Event       $event
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $searchFacets = $this->get('vimeet_infrastructure.repository.search_facet')
            ->getByEvent($event);

        $types = SearchFacet::getAllTypes();

        if (empty($searchFacets)) {
            foreach ($types as $type) {
                $searchFacets[] = new SearchFacet($event, $type);
            }
        };

        $command = new Update($searchFacets);

        $form    = $this->createForm(UpdateType::class, $command, [
            'submit' => true,
            'types'  => $types,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.admin.event.filter_facet.update.success');

            return $this->redirectToRoute('admin_event_search_facets', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Event/SearchFacet:update.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }
}
