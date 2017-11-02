<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Sheet;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrintTemplateController extends Controller
{
    /**
     * @param Request       $request
     * @param SheetTemplate $template
     *
     * @return Response
     */
    public function builderAction(Request $request, SheetTemplate $template)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $event = $template->getEvent();

        return $this->render('AdminBundle:SheetPrintTemplate:builder.html.twig', [
            'event'    => $event,
            'template' => $template,
            'locale'   => $template->getAvailableLocale($request->getLocale()),
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     * @param string        $locale
     *
     * @return JsonResponse
     */
    public function saveAction(Request $request, SheetTemplate $template, $locale)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        return new JsonResponse();
    }
}
