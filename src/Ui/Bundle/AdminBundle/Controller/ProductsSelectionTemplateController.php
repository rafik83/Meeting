<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProductsSelectionTemplateController extends Controller
{
    /**
     * @return Response
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $templates = [];

        return $this->render('AdminBundle:ProductsSelectionTemplate:list.html.twig', [
            'templates' => $templates,
        ]);
    }
}
