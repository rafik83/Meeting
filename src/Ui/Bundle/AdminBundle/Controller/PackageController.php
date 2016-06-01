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
use Proximum\Vimeet\Application\Command\Package\Update;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PackageController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $events            = $this->get('vimeet_infrastructure.repository.event_repository')->getEventsByAdmin($this->getUser());
        $packages = $this->get('repository.package_repository')->findByEvents($events);

        $create = new Create();
        $form   = $this->createForm(CreateType::class, $create, ['submit' => true, 'user' => $this->getUser()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_package_update', [
                'package' => $result->package->getId(),
            ]);
        }

        return $this->render('AdminBundle:Package:list.html.twig', [
            'packages' => $packages,
            'form'               => $form->createView(),
        ]);
    }

    /**
     * @param Request          $request
     * @param Package $package
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Package $package)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->getUser()->hasEvent($package->getEvent())) {
            throw $this->createAccessDeniedException('You are not allowed to edit this purchasing funnel.');
        }

        $update = new Update($package);
        $form   = $this->createForm(UpdateType::class, $update, [
            'event'  => $package->getEvent(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.template.package.update.success');

            return $this->redirectToRoute('admin_package_update', [
                'package' => $package->getId(),
            ]);
        }

        return $this->render('AdminBundle:Package:update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
