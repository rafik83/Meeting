<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Domain\Model\CategoryView;
use Proximum\Vimeet\Domain\Model\EventView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $categories = $this
            ->get('vimeet_infrastructure.repository.category_repository')
            ->getCategoryViewsByEventAndUser($eventView->id, $this->getUser(), $request->getLocale());

        return $this->render('VimeetAppBundle:Event/Catalog:categories.html.twig', [
            'eventView'  => $eventView,
            'categories' => $categories,
        ]);
    }

    /**
     * Display sheets matching category
     *
     * @param EventView    $eventView
     * @param CategoryView $categoryView
     *
     * @return Response
     */
    public function categoryAction(EventView $eventView, CategoryView $categoryView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheets  = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->search($categoryView->id, $this->getUser());

        return $this->render('VimeetAppBundle:Event/Catalog:category.html.twig', [
            'eventView'    => $eventView,
            'categoryView' => $categoryView,
            'sheets'       => $sheets,
        ]);
    }

    /**
     * Display a sheet
     *
     * @param EventView    $eventView
     * @param CategoryView $categoryView
     * @param Sheet        $sheet
     *
     * @return Response
     */
    public function sheetAction(EventView $eventView, CategoryView $categoryView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $data = $this->getSeeableData($this->getUser(), $sheet);

        $this->applySeeableData($sheet, $data);

        return $this->render('VimeetAppBundle:Event/Catalog:sheet.html.twig', [
            'eventView'    => $eventView,
            'categoryView' => $categoryView,
            'sheet'        => $sheet,
        ]);
    }

    /**
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return array
     */
    private function getSeeableData(User $user, Sheet $sheet)
    {
        $types = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypesByUser($user);

        foreach ($types as $type) {
            $sees = $this
                ->get('vimeet_infrastructure.repository.see_repository')
                ->getBySeerTypeAndSeeableType($type, $sheet->getType());

            foreach ($sees as $see) {
                return $see->getData();
            }

            return [];
        }


        return [];
    }

    private function applySeeableData($data, array $seeableData, $strategy = 'setnull'/*'unset'*/)
    {
        foreach ($seeableData as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->applySeeableData($data[$key], $value);
            } elseif ($value === true) {
                if ($strategy === 'seetnull') {
                    $data[$key] = null;
                } elseif ($strategy === 'unset') {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }
}
