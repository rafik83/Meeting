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
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateForEvent;
use Proximum\Vimeet\Application\Command\Sheet\Template\Duplicate;
use Proximum\Vimeet\Application\Command\Sheet\Template\Save;
use Proximum\Vimeet\Application\Command\Sheet\Template\Update;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\AddLocaleType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\CreateForEventType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\DuplicateForEventType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\DuplicateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\FilterSheetTemplateOrganizerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateController extends Controller
{
    /**
     * @param string $type
     * @param string $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method'          => 'GET',
            'csrf_protection' => false,
            'required'        => false,
        ]));
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $templates = $this->get('repository.template.sheet_template_repository')->getBaseTemplate();

        $create = new Create($request->getLocale());
        $form = $this->createForm(CreateType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_template_builder', [
                'template' => $result->template->getId(),
                'locale'   => $result->template->getFallback(),
            ]);
        }

        return $this->render('AdminBundle:Template:list.html.twig', [
            'templates' => $templates,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listOrganizerTemplateAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $filters    = [];
        $filterForm = $this->createFilterForm(FilterSheetTemplateOrganizerType::class, $filters, ['admin' => $this->getUser()]);
        $filtered   = $filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid();

        if ($filtered) {
            $filters = $filterForm->getData();
        }

        $filterFormView = $filterForm->createView();
        $filterSummary  = $this->get('filter_summary')->getFilters($filterFormView, $filters, $request->getLocale());

        $events             = $this->get('vimeet_infrastructure.repository.event_repository')->getListByAdmin($organizer);
        $baseTemplates      = $this->get('repository.template.sheet_template_repository')->getBaseTemplates();
        $organizerTemplates = $this->get('repository.template.sheet_template_repository')->getOrganizerTemplates($events, $filters);

        $create = new CreateForEvent();
        $form   = $this->createForm(CreateForEventType::class, $create, [
            'submit' => true,
            'admin'  => $organizer,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_template_builder', [
                'template' => $result->template->getId(),
                'locale'   => $result->template->getFallback(),
            ]);
        }

        return $this->render('AdminBundle:Template/Sheet:organizerList.html.twig', [
            'base_templates'      => $baseTemplates,
            'organizer_templates' => $organizerTemplates,
            'form'                => $form->createView(),
            'filter_form'         => $filterFormView,
            'filters_summary'     => $filterSummary,
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     *
     * @return RedirectResponse|Response
     */
    public function duplicateAction(Request $request, SheetTemplate $template)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $duplicate = new Duplicate($template, new \DateTime());
        $form      = $this->createForm(DuplicateType::class, $duplicate, [
            'action' => $this->generateUrl('admin_template_duplicate', ['template' => $template->getId()]),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($duplicate);

            return $this->redirectToRoute('admin_template_builder', [
                'template' => $result->template->getId(),
                'locale'   => $result->template->getFallback(),
            ]);
        }

        return $this->render('AdminBundle:Template:duplicate.html.twig', [
            'template' => $template,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     *
     * @return RedirectResponse|Response
     */
    public function duplicateOrganizerTemplateAction(Request $request, SheetTemplate $template)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $duplicate = new Duplicate($template, new \DateTime());

        $form      = $this->createForm(DuplicateForEventType::class, $duplicate, [
            'action' => $this->generateUrl('admin_organizer_template_duplicate', ['template' => $template->getId()]),
            'submit' => true,
            'admin'  => $this->getUser(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($duplicate);

            return $this->redirectToRoute('admin_template_builder', [
                'template' => $result->template->getId(),
                'locale'   => $result->template->getFallback(),
            ]);
        }

        return $this->render('AdminBundle:Template:duplicate.html.twig', [
            'template' => $template,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @param SheetTemplate $template
     * @param string        $locale
     *
     * @return Response
     */
    public function builderAction(SheetTemplate $template, $locale)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$this->getUser()->isSuperAdmin() && !$this->getUser()->hasEvent($template->getEvent())) {
            throw $this->createAccessDeniedException('You are not allowed to edit this template.');
        }

        if (!$template->hasLocale($locale)) {
            throw $this->createNotFoundException(sprintf('Locale "%s" does not exist on this template', $locale));
        }

        // Update form
        $updateForm = $this->createForm(UpdateType::class, new Update($template), [
            'action'   => $this->generateUrl('admin_template_update', ['template' => $template->getId(), 'locale' => $locale]),
            'submit'   => true,
            'template' => $template,
        ]);

        // Add locale form
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $addLocaleForm = $this->createForm(AddLocaleType::class, new AddLocale($template), [
                'action'   => $this->generateUrl('admin_template_add_locale', ['template' => $template->getId()]),
                'submit'   => true,
                'template' => $template,
            ]);
        } else {
            $addLocaleForm = null;
        }

        // Queries
        $nomenclatures = $this->get('repository.nomenclature_repository')->getAll();
        $completeness  = $this->get('sheet.template.completeness_calculator')->compute($template);
        $incompletes   = array_keys(array_filter($completeness, function ($percent) { return $percent < 100; }));

        // Add warning if some locales translations are incompletes
        if (!empty($incompletes)) {
            $this->addFlash('warning', 'flash.template.incomplete_translations.warning');
        }

        return $this->render('AdminBundle:Template:builder.html.twig', [
            'template'        => $template,
            'locale'          => $locale,
            'update_form'     => $updateForm->createView(),
            'add_locale_form' => $addLocaleForm ? $addLocaleForm->createView() : null,
            'completeness'    => $completeness,
            'nomenclatures'   => $nomenclatures
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     *
     * @return RedirectResponse
     */
    public function addLocaleAction(Request $request, SheetTemplate $template)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $addLocale     = new AddLocale($template);
        $addLocaleForm = $this->createForm(AddLocaleType::class, $addLocale, [
            'action'   => $this->generateUrl('admin_template_add_locale', ['template' => $template->getId()]),
            'submit'   => true,
            'template' => $template,
        ]);

        if ($addLocaleForm->handleRequest($request)->isSubmitted()) {
            if ($addLocaleForm->isValid()) {
                $this->get('tactician.commandbus')->handle($addLocale);

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
            'locale'   => $template->getFallback(),
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     * @param string        $locale
     *
     * @return JsonResponse
     */
    public function saveAction(Request $request, SheetTemplate $template, $locale)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$this->getUser()->isSuperAdmin() && !$this->getUser()->hasEvent($template->getEvent())) {
            throw $this->createAccessDeniedException('You are not allowed to edit this template.');
        }

        if (!$template->hasLocale($locale)) {
            return new JsonResponse(['error' => sprintf('Locale "%s" does not exist on this template', $locale)], 404);
        }

        $config = json_decode($request->getContent(), true);
        $this->get('tactician.commandbus')->handle(new Save($template, $config));

        return new JsonResponse();
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     * @param string        $locale
     *
     * @return RedirectResponse
     */
    public function updateAction(Request $request, SheetTemplate $template, $locale)
    {
        $command = new Update($template);
        $form = $this->createForm(UpdateType::class, $command, [
            'action'   => $this->generateUrl('admin_template_update', ['template' => $template->getId(), 'locale' => $locale]),
            'submit'   => true,
            'template' => $template,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
        }

        return $this->redirectToRoute('admin_template_builder', [
            'template' => $template->getId(),
            'locale'   => $locale,
        ]);
    }
}
