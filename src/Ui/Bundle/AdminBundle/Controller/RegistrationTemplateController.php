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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationTemplateController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $templates = $this->get('repository.template.registration_template_repository')->getBaseTemplate();

        return $this->render('AdminBundle:Template:list.html.twig', [
            'templates' => $templates,
            'form'      => $form->createView(),
        ]);
    }
}
