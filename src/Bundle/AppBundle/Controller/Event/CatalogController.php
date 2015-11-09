<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends Controller
{
    /**
     * Display catalog categories of an event
     *
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response
     */
    public function categoriesAction(Request $request, EventView $eventView)
    {
        $categories = $this
            ->get('vimeet_infrastructure.repository.category_repository')
            ->getCategoryViewsByEvent($eventView->id, $request->getLocale());

        return $this->render('VimeetAppBundle:Event/Catalog:categories.html.twig', [
            'eventView'  => $eventView,
            'categories' => $categories,
        ]);
    }

    /**
     * Display sheets matching category
     *
     * @param EventView $eventView
     * @param Category  $category
     *
     * @return Response
     */
    public function categoryAction(EventView $eventView, Category $category)
    {
        $filters = $category->getFilters();
        $sheets  = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->search($filters);

        return $this->render('VimeetAppBundle:Event/Catalog:category.html.twig', [
            'eventView'  => $eventView,
            'category'   => $category,
            'sheets'     => $sheets,
        ]);
    }
}
