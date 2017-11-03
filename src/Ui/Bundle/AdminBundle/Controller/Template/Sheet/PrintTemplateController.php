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
        $this->checkUserCanEdit($template);

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
     *
     * @return JsonResponse
     */
    public function saveAction(Request $request, SheetTemplate $template)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->checkUserCanEdit($template);

        $config = json_decode($request->getContent(), true);

        return new JsonResponse();
    }

    /**
     * @param SheetTemplate $template
     */
    private function checkUserCanEdit(SheetTemplate $template)
    {
        if (!$this->getUser()->isSuperAdmin() && !$this->getUser()->hasEvent($template->getEvent())) {
            throw $this->createAccessDeniedException('You are not allowed to edit this template.');
        }
    }
}
