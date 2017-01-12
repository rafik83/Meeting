<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planner;

use Proximum\Vimeet\Application\Command\Planner\Import;
use Proximum\Vimeet\Application\Exception\Planner\InvalidXmlException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planner\ImportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response|RedirectResponse
     */
    public function importAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $import = new Import($event);
        $form   = $this->createForm(ImportType::class, $import, [
            'submit'  => true,
            'confirm' => 'form.planner_import.confirm.submit.label',
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($import);
                $this->addFlash('success', 'flash.admin.planner.import.success');

                return $this->redirectToRoute('admin_planner', [
                    'event' => $event->getId(),
                ]);
            } catch (InvalidXmlException $exception) {
                $form->get('file')->addError(
                    new FormError(
                        'validators.planner.import.invalidXml'
                    )
                );
            }
        }

        return $this->render('AdminBundle:Planner/Import:form.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
