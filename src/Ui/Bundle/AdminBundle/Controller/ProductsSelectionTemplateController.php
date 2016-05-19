<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Template\ProductsSelection\CreateForEvent;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\ProductsSelection\CreateForEventType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductsSelectionTemplateController extends Controller
{
    /**
     * @return Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $events = $this->get('vimeet_infrastructure.repository.event_repository')->getListByAdmin($this->getUser());

        $templates = $this
            ->get('repository.template.products_selection_template_repository')
            ->getTemplateForGivenEvents($events);

        $create = new CreateForEvent();
        $form   = $this->createForm(CreateForEventType::class, $create, [
            'submit' => true,
            'user'   => $this->getUser(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_template_products_selection_list');
        }

        return $this->render('AdminBundle:ProductsSelectionTemplate:list.html.twig', [
            'templates' => $templates,
            'form'      => $form->createView(),
        ]);
    }
}
