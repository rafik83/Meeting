<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Sheet\Template\Create;
use Proximum\Vimeet\Application\Command\Sheet\Template\Duplicate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\DuplicateType;
use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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

            return $this->redirectToRoute('admin_template_builder', [
                'template' => $result->template->getId(),
                'locale'   => $request->getLocale(),
            ]);
        }

        return $this->render('AdminBundle:Template:list.html.twig', [
            'templates' => $templates,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Template $template
     *
     * @return RedirectResponse|Response
     */
    public function duplicateAction(Request $request, Template $template)
    {
        $duplicate = new Duplicate($template);
        $form      = $this->createForm(DuplicateType::class, $duplicate, [
            'action' => $this->generateUrl('admin_template_duplicate', ['template' => $template->getId()]),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('command.sheet.template.duplicate_handler')->handle($duplicate);

            return $this->redirectToRoute('admin_template_builder', ['template' => $result->template->getId()]);
        }

        return $this->render('AdminBundle:Template:duplicate.html.twig', [
            'template' => $template,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @param Template $template
     * @param string   $locale
     *
     * @return Response
     */
    public function builderAction(Template $template, $locale)
    {
        return $this->render('AdminBundle:Template:builder.html.twig', [
            'template' => $template,
            'locale'   => $locale,
        ]);
    }

    /**
     * @param Request  $request
     * @param Template $template
     * @param string   $locale
     *
     * @return JsonResponse
     */
    public function saveAction(Request $request, Template $template, $locale)
    {
        $config = json_decode($request->getContent(), true);

        $this->get('repository.sheet.template_repository')->set($template->setValue($config));

        return new JsonResponse();
    }
}
