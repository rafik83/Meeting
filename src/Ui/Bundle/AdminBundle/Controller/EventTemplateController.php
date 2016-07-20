<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class EventTemplateController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function registrationTemplateAction(Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $templates = $this->get('repository.template.registration_template_repository')
                          ->getTemplateForGivenEvent($event);

        return $this->render('AdminBundle:RegistrationTemplate:list.html.twig', [
            'templates' => $templates,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function sheetTemplateAction(Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $templates = $this->get('repository.template.sheet_template_repository')
                          ->getTemplateForGivenEvent($event);

        return $this->render('AdminBundle:SheetTemplate:list.html.twig', [
            'templates' => $templates,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function packageTemplateAction(Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $templates = $this->get('repository.package_repository')->findByEvent($event);

        return $this->render('', [
            'templates' => $templates,
        ]);
    }
}
