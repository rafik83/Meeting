<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PackageController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function listAction(Event $event)
    {
        $packages = $this->get('repository.package_repository')->findByEvent($event);

        return $this->render(
            'AdminBundle:Package:list.html.twig',
            [
                'event'    => $event,
                'packages' => $packages,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Event $event
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event)
    {
        $create = new Create($event);
        $form   = $this->createForm(CreateType::class, $create, [
            'method' => 'POST',
            'event'  => $event,
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.package.create.success');

            return $this->redirectToRoute('admin_package', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render(
            'AdminBundle:Package:create.html.twig', [
                'event' => $event,
                'form'  => $form->createView()
            ]
        );
    }
}
