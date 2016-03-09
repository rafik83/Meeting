<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\FilterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
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
        $filters  = [];
        $form     = $this->createFilterForm(FilterType::class, $filters);
        $filtered = $form->handleRequest($request)->isSubmitted() && $form->isValid();

        if ($filtered) {
            $filters = $form->getData();
        }

        $sheets = $this
            ->get('query.sheet.sheet_list_view_factory')
            ->paginate($event, $filters, $request->query->getInt('page', 1), 20, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Sheet:list.html.twig', [
            'event'    => $event,
            'sheets'   => $sheets,
            'form'     => $form->createView(),
            'filtered' => $filtered,
        ]);
    }

    /**
     * @param string $type
     * @param string $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method'          => 'GET',
            'csrf_protection' => false,
            'required'        => false,
        ]));
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return Response
     */
    public function detailsAction(Request $request, Event $event, Sheet $sheet)
    {
        $details = $this->get('sheet.sheet_details_view_factory')->create($sheet, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Sheet:details.html.twig', [
            'event'   => $event,
            'sheet'   => $sheet,
            'details' => $details,
        ]);
    }
}
