<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Template\Registration\AddLocale;
use Proximum\Vimeet\Application\Command\Template\Registration\Update;
use Proximum\Vimeet\Domain\Exception\Nomenclature\NomenclatureNotFoundException;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Template\Exception\TemplateException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\AddLocaleType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Registration\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationTemplateController extends Controller
{
    /**
     * @return Response
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $templates          = $this->get('repository.template.registration_template_repository')->getBaseTemplates();
        $events             = $this->get('vimeet_infrastructure.repository.event_repository')->getListByAdmin($this->getUser());
        $templatesOrganizer = $this->get('repository.template.registration_template_repository')->getTemplateForGivenEvents($events);

        return $this->render('AdminBundle:RegistrationTemplate:list.html.twig', [
            'templates'          => $templates,
            'templatesOrganizer' => $templatesOrganizer,
        ]);
    }

    /**
     * @param Request              $request
     * @param RegistrationTemplate $template
     * @param string               $locale
     *
     * @return Response
     */
    public function builderAction(Request $request, RegistrationTemplate $template, $locale)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $update     = new Update($template);
        $updateForm = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
        ]);

        $addLocaleForm = null;

        if (!$template->getEvent()) {
            $addLocaleForm = $this->createForm(AddLocaleType::class, new AddLocale($template), [
                'action'   => $this->generateUrl('admin_template_registration_add_locale',
                    ['template' => $template->getId()]),
                'submit'   => true,
                'template' => $template,
            ]);
        }

        $completeness  = $this->get('sheet.template.completeness_calculator')->compute($template);
        $incompletes   = array_keys(array_filter($completeness, function ($percent) { return $percent < 100; }));

        if ($updateForm->handleRequest($request)->isSubmitted() && $updateForm->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($update);
                $this->addFlash('success', 'flash.admin.template.registration.update.success');

                return $this->redirectToRoute('admin_template_registration_builder', [
                    'template' => $template->getId(),
                    'locale'   => $locale,
                ]);
            } catch (NomenclatureNotFoundException $exception) {
                $this->addFlash('error', 'flash.admin.template.registration.update.error.nomenclatureNotFound');
                $updateForm->get('value')->addError(
                    new FormError(
                        $this->get('translator')->trans('validators.admin.template.registration.update.error.nomenclatureNotFound', [], 'validators')
                    )
                );
            } catch (TemplateException $exception) {
                $this->addFlash('error', 'flash.admin.template.registration.update.error.template');
                $updateForm->get('value')->addError(
                    new FormError(
                        $this->get('translator')->trans('validators.admin.template.registration.update.error.template', [], 'validators')
                    )
                );
            }
        }

        // Add warning if some locales translations are incompletes
        if (!empty($incompletes)) {
            $this->addFlash('warning', 'flash.template.incomplete_translations.warning');
        }

        return $this->render('AdminBundle:RegistrationTemplate:builder.html.twig', [
            'template'        => $template,
            'locale'          => $locale,
            'form'            => $updateForm->createView(),
            'add_locale_form' => $addLocaleForm ? $addLocaleForm->createView() : null,
            'completeness'    => $completeness,
            'event'           => $template->getEvent(),
        ]);
    }


    /**
     * @param Request              $request
     * @param RegistrationTemplate $template
     *
     * @return RedirectResponse
     */
    public function addLocaleAction(Request $request, RegistrationTemplate $template)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $addLocale     = new AddLocale($template);
        $addLocaleForm = $this->createForm(AddLocaleType::class, $addLocale, [
            'action'   => $this->generateUrl('admin_template_registration_add_locale', ['template' => $template->getId()]),
            'submit'   => true,
            'template' => $template,
        ]);

        if ($addLocaleForm->handleRequest($request)->isSubmitted()) {
            if ($addLocaleForm->isValid()) {
                $this->get('tactician.commandbus')->handle($addLocale);

                return $this->redirectToRoute('admin_template_registration_builder', [
                    'template' => $template->getId(),
                    'locale'   => $addLocale->locale,
                ]);
            } else {
                $this->addFlash('error', (string) $addLocaleForm->getErrors(true));
            }
        }

        return $this->redirectToRoute('admin_template_sheet_builder', [
            'template' => $template->getId(),
            'locale'   => $template->getFallback(),
        ]);
    }
}
