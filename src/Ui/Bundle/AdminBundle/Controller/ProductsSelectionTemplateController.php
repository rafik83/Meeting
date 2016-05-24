<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Template\ProductsSelection\Create;
use Proximum\Vimeet\Application\Command\Template\ProductsSelection\Update;
use Proximum\Vimeet\Domain\Model\Template\ProductsSelectionTemplate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\ProductsSelection\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\ProductsSelection\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductsSelectionTemplateController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $events = $this->get('vimeet_infrastructure.repository.event_repository')->getListByAdmin($this->getUser());

        $templates = $this
            ->get('repository.template.products_selection_template_repository')
            ->getTemplateForGivenEvents($events);

        $create = new Create();
        $form   = $this->createForm(CreateType::class, $create, [
            'submit' => true,
            'user'   => $this->getUser(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $template = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_template_products_selection_update', [
                'template' => $template->getId(),
            ]);
        }

        return $this->render('AdminBundle:ProductsSelectionTemplate:list.html.twig', [
            'templates' => $templates,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request                   $request
     * @param ProductsSelectionTemplate $template
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, ProductsSelectionTemplate $template)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$this->getUser()->isSuperAdmin() && !$this->getUser()->hasEvent($template->getEvent())) {
            throw $this->createAccessDeniedException('You are not allowed to edit this template.');
        }

        $locale = $template->getEvent()->getAvailableLocale($request->getLocale());

        $templateData = $this->get('template.template_data_factory')->create(
            $template->getValue(),
            [],
            $locale,
            $template->getFallback()
        );

        $update = new Update($template, $templateData);
        $form   = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);

            $this->addFlash('success', 'flash.admin.template.products_selection.update.success');

            return $this->redirectToRoute('admin_template_products_selection_list');
        }

        return $this->render('AdminBundle:ProductsSelectionTemplate:update.html.twig', [
            'template' => $templateData,
            'locale'   => $locale,
            'form'     => $form->createView(),
        ]);
    }
}
