<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Sheet\Template\AddLocale;
use Proximum\Vimeet\Application\Command\Sheet\Template\Create;
use Proximum\Vimeet\Application\Command\Sheet\Template\Duplicate;
use Proximum\Vimeet\Application\Command\Sheet\Template\Save;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\AddLocaleType;
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
        $templates = $this->get('repository.sheet.template_repository')->getBaseTemplate();

        $create = new Create($request->getLocale());
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
        if (!$template->hasLocale($locale)) {
            throw $this->createNotFoundException(sprintf('Locale "%s" does not exist on this template', $locale));
        }

        $addLocale     = new AddLocale($template);
        $addLocaleForm = $this->createForm(AddLocaleType::class, $addLocale, [
            'action'   => $this->generateUrl('admin_template_add_locale', ['template' => $template->getId()]),
            'submit'   => true,
            'template' => $template,
        ]);

        return $this->render('AdminBundle:Template:builder.html.twig', [
            'template'        => $template,
            'locale'          => $locale,
            'add_locale_form' => $addLocaleForm->createView()
        ]);
    }

    /**
     * @param Request  $request
     * @param Template $template
     *
     * @return RedirectResponse
     */
    public function addLocaleAction(Request $request, Template $template)
    {
        $addLocale     = new AddLocale($template);
        $addLocaleForm = $this->createForm(AddLocaleType::class, $addLocale, [
            'action'   => $this->generateUrl('admin_template_add_locale', ['template' => $template->getId()]),
            'submit'   => true,
            'template' => $template,
        ]);

        if ($addLocaleForm->handleRequest($request)->isSubmitted()) {
            if ($addLocaleForm->isValid()) {
                $this->get('command.sheet.template.add_locale_handler')->handle($addLocale);

                return $this->redirectToRoute('admin_template_builder', [
                    'template' => $template->getId(),
                    'locale'   => $addLocale->locale,
                ]);
            } else {
                $this->addFlash('error', (string) $addLocaleForm->getErrors(true));
            }
        }

        return $this->redirectToRoute('admin_template_builder', [
            'template' => $template->getId(),
            'locale'   => $template->getFirstLocale(),
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
        if (!$template->hasLocale($locale)) {
            return new JsonResponse(['error' => sprintf('Locale "%s" does not exist on this template', $locale)], 404);
        }

        $config = json_decode($request->getContent(), true);
        $this->get('command.sheet.template.save_handler')->handle(new Save($template, $config));

        return new JsonResponse();
    }
}
