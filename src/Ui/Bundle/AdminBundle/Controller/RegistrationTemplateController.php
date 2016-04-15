<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Template\Registration\Update;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Registration\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $templatesOrganizer = $this->get('repository.template.registration_template_repository')->getAllOrganizersTemplates();

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
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $update     = new Update($template);
        $updateForm = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
        ]);

        if ($updateForm->handleRequest($request)->isSubmitted() && $updateForm->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.template.registration.update.success');

            return $this->redirectToRoute('admin_template_registration_list');
        }

        return $this->render('AdminBundle:RegistrationTemplate:builder.html.twig', [
            'template' => $template,
            'locale'   => $locale,
            'form'     => $updateForm->createView(),
        ]);
    }
}
