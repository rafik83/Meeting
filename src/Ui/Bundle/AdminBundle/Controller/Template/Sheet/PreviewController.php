<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\Template\UpdatePreview;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\Exception\TemplateException;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\PreviewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewController extends Controller
{
    /**
     * @param Request       $request
     * @param SheetTemplate $template
     *
     * @return Response
     */
    public function updatePreviewAction(Request $request, SheetTemplate $template)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        $templateDataFactory = $this->get('template.template_data_factory');
        $templateData    = $templateDataFactory->createFromTemplate($template);
        $templateObjects = $templateDataFactory->getPreviewAvailableData($templateData);

        if (null === $template->getEvent()) {
            $locale = $template->getAvailableLocale($request->getLocale());
        } else {
            $locale = $template->getEvent()->getAvailableLocale($request->getLocale());
        }

        $command = new UpdatePreview($template, $templateObjects);

        $form = $this->createForm(PreviewType::class, $command, [
            'templateData'    => $templateData,
            'templateObjects' => $templateObjects,
            'locale'          => $locale,
            'submit'          => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($command);
                $this->addFlash('success', 'flash.template.preview.update.success');

                return $this->redirectToRoute('admin_template_sheet_preview_update', [
                    'template' => $template->getId(),
                ]);
            } catch (TemplateException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('AdminBundle:SheetTemplate:preview.html.twig', [
            'form'   => $form->createView(),
            'event'  => $template->getEvent(),
            'locale' => $locale,
        ]);
    }
}
