<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Catalog\External\Configure;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\SearchFacetQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Catalog\ConfigureType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class CatalogController extends Controller
{
    /**
     * @param Request       $request
     * @param Event         $event
     * @param UserInterface $user
     *
     * @return Response
     */
    public function configureAction(Request $request, Event $event, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $searchFacetQuery = new SearchFacetQuery($event);
        $searchFacets = $this->get('query.catalog.external.search_facet_query')->handle($searchFacetQuery);

        $catalogVisibilityView = $this
            ->get('query.catalog.external.catalog_visibility_view_query_handler')
            ->handle(new CatalogVisibilityViewQuery($event));

        $configure = new Configure($event, $catalogVisibilityView, $searchFacets);

        $configureForm = $this->createForm(ConfigureType::class, $configure, [
            'user' => $user,
            'event' => $event,
            'locale' => $locale,
            'types' => $searchFacetQuery->types,
        ]);

        if ($configureForm->handleRequest($request)->isSubmitted() && $configureForm->isValid()) {
            $this->get('catalog.external.configure_handler')->handle($configure);
            $this->addFlash('success', 'flash.admin.event.catalog.external.configure.success');

            return $this->redirectToRoute('admin_event_external_catalog_configure', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Catalog/External:configure.html.twig', [
            'event' => $event,
            'form' => $configureForm->createView(),
        ]);
    }
}
