<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planner;

use Proximum\Vimeet\Application\Command\Planner\ExportJobCreator;
use Proximum\Vimeet\Application\Exception\Planner\DayNotConfiguredException;
use Proximum\Vimeet\Application\Exception\Planner\SlotNotConfiguredException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\XmlFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planner\ExportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends Controller
{
    /**
     * @param Request       $request
     * @param UserInterface $admin
     * @param Event         $event
     *
     * @return RedirectResponse
     */
    public function exportAction(Request $request, UserInterface $admin, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$admin instanceof Admin) {
            throw $this->createNotFoundException('Admin not found');
        }

        $exportJobCreator = new ExportJobCreator($event, $admin, $request->getLocale());

        $form   = $this->createForm(ExportType::class, $exportJobCreator, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($exportJobCreator);

                $this->addFlash('success', 'flash.admin.planner.export.success');

                return $this->redirectToRoute('admin_export_planner_data', ['event' => $event->getId()]);

            } catch(SlotNotConfiguredException $exception) {
                $this->addFlash('error', sprintf('flash.%s', $exception->getMessage()));
            } catch(DayNotConfiguredException $exception) {
                $this->addFlash('error', sprintf('flash.%s', $exception->getMessage()));
            } catch (UnableToDispatchException $exception) {
                $this->addFlash('error', sprintf('flash.%s', $exception->indication));
            }
        }

        return $this->render('AdminBundle:Planner/Export:form.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Event  $event
     * @param string $hash
     * @param File   $file
     *
     * @return XmlFileResponse
     */
    public function exportFileAction(Event $event, $hash, File $file)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($file->getHash() !== $hash) {
            throw $this->createNotFoundException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        $path = sprintf('%s%s', $this->getParameter('infrastructure.export_planner_path'), $file->getPath());

        if (!$this->get('filesystem')->exists($path)) {
            throw $this->createNotFoundException(sprintf('File %s not found', $file->getId()));
        }

        return new XmlFileResponse(
            file_get_contents($path),
            sprintf("export_planner_%s_%s.xml", $event->getId(), date("Y_m_d_His"))
        );
    }
}
