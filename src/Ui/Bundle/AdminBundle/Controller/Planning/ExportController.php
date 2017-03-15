<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planning;

use Proximum\Vimeet\Application\Command\Planning\ExportPlanningJobCreator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planning\ExportPlanningType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends Controller
{
    /**
     * @param Request       $request
     * @param UserInterface $admin
     * @param Event         $event
     *
     * @return Response|RedirectResponse
     */
    public function exportPlanningAction(Request $request, UserInterface $admin, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$admin instanceof Admin) {
            throw $this->createNotFoundException('Admin not found');
        }

        $exportPlanning = new ExportPlanningJobCreator($admin, $request->getLocale());
        $form           = $this->createForm(ExportPlanningType::class, $exportPlanning, [
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'user'   => $admin,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($exportPlanning);
            $this->addFlash('success', 'flash.admin.planning.export.success');

            return $this->redirectToRoute('admin_planning_export', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Planning:export.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Event  $event
     * @param string $hash
     * @param File   $file
     *
     * @return Response
     */
    public function exportPlanningPrintAction(Event $event, $hash, File $file)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($file->getHash() !== $hash) {
            throw $this->createNotFoundException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        $path = sprintf('%s/%s', $this->getParameter('infrastructure.print_planning_path'), $file->getPath());

        if (!file_exists($path)) {
            throw $this->createNotFoundException(sprintf('File %s not found', $file->getId()));
        }

        return new Response(file_get_contents($path));
    }
}
