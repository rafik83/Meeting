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
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateForEvent;
use Proximum\Vimeet\Application\Command\Sheet\Template\Duplicate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\CreateForEventType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\DuplicateForEventType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\DuplicateType;
use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\FilterSheetTemplateOrganizerType;
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
     * @return Response
     */
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $templates = $this->get('repository.sheet.template_repository')->getBaseTemplate();

        $create = new Create(new \DateTime());
        $form = $this->createForm(CreateType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('command.sheet.template.create_handler')->handle($create);

            return $this->redirectToRoute('admin_template_builder', ['template' => $result->template->getId()]);
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
        $organizer = $this->getUser();
        if (!$organizer->isOrganizer()) {
            throw $this->createAccessDeniedException(
                sprintf('%s is not a granted ROLE to access this page', $organizer->getRole())
            );
        }

        $filters    = [];
        $filterForm = $this->createFilterForm(FilterSheetTemplateOrganizerType::class, $filters, [
            'admin'  => $organizer,
        ]);
        $filtered   = $filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid();

        if ($filtered) {
            $filters = $filterForm->getData();
        }

        $events    = $this->get('vimeet_infrastructure.repository.event_repository')->getListByAdmin($organizer);
        $templates = $this->get('repository.sheet.template_repository')->listOrganizerTemplate($events, $filters);

        $create = new CreateForEvent(new \DateTime());
        $form   = $this->createForm(CreateForEventType::class, $create, [
            'submit' => true,
            'admin'  => $organizer,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('command.sheet.template.create_for_event_handler')->handle($create);

            return $this->redirectToRoute('admin_template_builder', ['template' => $result->template->getId()]);
        }

        return $this->render('AdminBundle:Template/Sheet:organizerList.html.twig', [
            'templates'   => $templates,
            'form'        => $form->createView(),
            'filter_form' => $filterForm->createView(),
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
        $duplicate = new Duplicate($template, new \DateTime());
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
     * @param Request  $request
     * @param Template $template
     *
     * @return RedirectResponse|Response
     */
    public function duplicateOrganizerTemplateAction(Request $request, Template $template)
    {
        $duplicate = new Duplicate($template, new \DateTime());

        $form      = $this->createForm(DuplicateForEventType::class, $duplicate, [
            'action' => $this->generateUrl('admin_organizer_template_duplicate', ['template' => $template->getId()]),
            'submit' => true,
            'admin'  => $this->getUser(),
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
     *
     * @return Response
     */
    public function builderAction(Template $template)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $admin = $this->getUser();
        if (!$admin->isSuperAdmin()) {
            $events = $this->get('vimeet_infrastructure.repository.event_repository')->getEventsByAdmin($admin);

            if (!in_array($template->getEvent(), $events)) {
                throw $this->createAccessDeniedException(
                    sprintf('%s %s %s is not an authorized admin to edit this template', $admin->getRole(), $admin->getEmail(), $admin->getDisplayName())
                );
            }
        }

        return $this->render('AdminBundle:Template:builder.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @param Request  $request
     * @param Template $template
     *
     * @return JsonResponse
     */
    public function saveAction(Request $request, Template $template)
    {
        $config = json_decode($request->getContent(), true);

        $this->get('repository.sheet.template_repository')->set($template->setValue($config));

        return new JsonResponse();
    }
}
