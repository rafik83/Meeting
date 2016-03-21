<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Sheet\Template\Create;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\Template\CreateType;
use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateController extends Controller
{
    /**
     * @return Response
     */
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $templates = $this->get('repository.sheet.template_repository')->all();

        $create = new Create();
        $form = $this->createForm(CreateType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('command.sheet.template.create_handler')->handle($create);

            return $this->redirectToRoute('admin_template_builder', ['template' => $result->template->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Template:list.html.twig', [
            'templates' => $templates,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Template $template
     *
     * @return Response
     */
    public function builderAction(Template $template)
    {
        return $this->render('VimeetAppBundle:Admin/Template:builder.html.twig', [
            'template' => $template,
        ]);
    }
}
