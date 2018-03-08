<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\Template\SavePrintTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
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
    public function builderAction(Request $request, SheetTemplate $template): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        $resolvedPrintTemplateView = $this->get('template.print_template_resolver')->resolve($template);

        return $this->render('AdminBundle:SheetPrintTemplate:builder.html.twig', [
            'event'          => $template->getEvent(),
            'templateId'     => $template->getId(),
            'templateTitle'  => $template->getTitle(),
            'locale'         => $template->getAvailableLocale($request->getLocale()),
            'printValue'     => $resolvedPrintTemplateView->printValueResolved,
            'missingObjects' => $resolvedPrintTemplateView->missingObjects,
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     *
     * @return JsonResponse
     */
    public function saveAction(Request $request, SheetTemplate $template): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        $config = json_decode($request->getContent(), true);
        $this->get('tactician.commandbus')->handle(new SavePrintTemplate($template, $config));

        return new JsonResponse();
    }
}
