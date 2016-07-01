<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Components\Rule\Exception\NoRuleFoundException;
use Proximum\Vimeet\Application\Components\Rule\Strategy\SetNullStrategy;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\CategoryView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends Controller
{
    /**
     * Display catalog categories of an event.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function categoriesAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $categories = $this
            ->get('vimeet_infrastructure.repository.category_repository')
            ->getCategoryViewsByEventAndUser($eventDomain->getEvent(), $this->getUser(), $request->getLocale());

        return $this->render('EventBundle:Catalog:categories.html.twig', [
            'event'      => $eventDomain->getEvent(),
            'categories' => $categories,
        ]);
    }

    /**
     * Display sheets matching category.
     *
     * @param EventDomain  $eventDomain
     * @param CategoryView $categoryView
     *
     * @return Response
     */
    public function categoryAction(EventDomain $eventDomain, CategoryView $categoryView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheets = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->search($categoryView->id, $this->getUser());

        array_walk($sheets, function (Sheet &$sheet) {
            $rule = $this
                ->get('vimeet_infrastructure.application.components.rule.manager')
                ->getRule($sheet, $this->getUser());

            $this
                ->get('vimeet_infrastructure.application.components.rule.manager')
                ->apply($rule, $sheet, new SetNullStrategy());
        });

        return $this->render('EventBundle:Catalog:category.html.twig', [
            'event'        => $eventDomain->getEvent(),
            'categoryView' => $categoryView,
            'sheets'       => $sheets,
        ]);
    }

    /**
     * Display a sheet.
     *
     * @param EventDomain  $eventDomain
     * @param CategoryView $categoryView
     * @param Sheet        $sheet
     *
     * @return Response
     */
    public function sheetAction(EventDomain $eventDomain, CategoryView $categoryView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $sheetView = $this
                ->get('vimeet_infrastructure.application.components.sheet.manager')
                ->getSheetDataViewByUser($this->getUser(), $sheet);

            $sheetAllowedForMeetingRequest = $this
                ->get('vimeet_infrastructure.application.components.sheet.manager')
                ->getUserSheetsThatCanSeeTheGivenSheet($this->getUser(), $sheet);

            return $this->render('EventBundle:Catalog:sheet.html.twig', [
                'event'                         => $eventDomain->getEvent(),
                'categoryView'                  => $categoryView,
                'sheet'                         => $sheetView,
                'sheetAllowedForMeetingRequest' => $sheetAllowedForMeetingRequest,
            ]);
        } catch (NoRuleFoundException $exception) {
            throw $this->createNotFoundException($exception->getMessage(), $exception);
        }
    }
}
