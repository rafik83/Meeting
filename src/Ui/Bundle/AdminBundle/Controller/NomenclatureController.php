<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Behat\Transliterator\Transliterator;
use Proximum\Vimeet\Application\Command\Nomenclature\Assign;
use Proximum\Vimeet\Application\Command\Nomenclature\AssignResult;
use Proximum\Vimeet\Application\Command\Nomenclature\Create;
use Proximum\Vimeet\Application\Command\Nomenclature\CreateResult;
use Proximum\Vimeet\Application\Command\Nomenclature\Exception\MissingKeysException;
use Proximum\Vimeet\Application\Command\Nomenclature\Import;
use Proximum\Vimeet\Application\Command\Nomenclature\Update;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\BadCharsetException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\ImportException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Data\Nomenclature\ExportData;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Data\Nomenclature\ImportData;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\AssignType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\ExportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\ImportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class NomenclatureController extends Controller
{
    /**
     * List globals nomenclatures
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function globalsAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $command = new Create();
        $form    = $this->createForm(CreateType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

            /** @var CreateResult $result */
            $result = $this->get('tactician.commandbus')->handle($command);

            return $this->redirectToRoute('admin_nomenclature_read', ['nomenclature' => $result->nomenclature->getId()]);
        }

        $repository    = $this->get('repository.nomenclature_repository');
        $nomenclatures = $repository->findGlobals();

        return $this->render('AdminBundle:Nomenclature:globals.html.twig', [
            'form'          => $form->createView(),
            'nomenclatures' => $nomenclatures,
        ]);
    }

    /**
     * List event nomenclature
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function eventAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Create($event);
        $form    = $this->createForm(CreateType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            /** @var CreateResult $result */
            $result = $this->get('tactician.commandbus')->handle($command);

            return $this->redirectToRoute('admin_nomenclature_read', ['nomenclature' => $result->nomenclature->getId()]);
        }

        $repository    = $this->get('repository.nomenclature_repository');
        $nomenclatures = $repository->findByEvent($event);

        return $this->render('AdminBundle:Nomenclature:event.html.twig', [
            'event'         => $event,
            'form'          => $form->createView(),
            'nomenclatures' => $nomenclatures,
        ]);
    }

    /**
     * Display the Nomenclature, update form and import/export feature
     *
     * @param Request      $request
     * @param Nomenclature $nomenclature
     *
     * @return RedirectResponse|Response
     */
    public function readAction(Request $request, Nomenclature $nomenclature)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        if ($nomenclature->getEvent()) {
            $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $nomenclature->getEvent());
        }

        // Handle update
        $update     = new Update($nomenclature);
        $updateForm = $this->createForm(UpdateType::class, $update, ['submit' => true]);

        if ($url = $this->handleUpdate($request, $updateForm, $update)) {
            return $this->redirect($url);
        }

        // Handle import
        $import     = new ImportData($nomenclature);
        $importForm = $this->createForm(ImportType::class, $import);

        if ($url = $this->handleImport($request, $importForm, $import)) {
            return $this->redirect($url);
        }

        // Handle assign
        $assign     = new Assign($nomenclature);
        $assignForm = $this->createForm(AssignType::class, $assign, ['submit' => true, 'admin' => $this->getUser()]);

        if ($url = $this->handleAssign($request, $assignForm, $assign)) {
            return $this->redirect($url);
        }

        // Handle export
        $export     = new ExportData($nomenclature);
        $exportForm = $this->createExportForm($export);

        return $this->render('AdminBundle:Nomenclature:read.html.twig', [
            'update_form'  => $updateForm->createView(),
            'import_form'  => $importForm->createView(),
            'assign_form'  => $assignForm->createView(),
            'export_form'  => $exportForm->createView(),
            'nomenclature' => $nomenclature,
        ]);
    }

    /**
     * Handle Nomenclature update
     *
     * @param Request       $request
     * @param FormInterface $form
     * @param Update        $data
     *
     * @return null|string
     */
    private function handleUpdate(Request $request, FormInterface $form, Update $data)
    {
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessNomenclatureAccess($data->nomenclature);

            $this->get('tactician.commandbus')->handle($data);
            $this->addFlash('success', 'flash.admin.nomenclature.update.success');

            return $this->generateUrl('admin_nomenclature_read', ['nomenclature' => $data->nomenclature->getId()]);
        }

        return null;
    }

    /**
     * Handle csv import of a Nomenclature
     *
     * @param Request       $request
     * @param FormInterface $form
     * @param ImportData    $data
     *
     * @return null|string
     */
    private function handleImport(Request $request, FormInterface $form, ImportData $data)
    {
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessNomenclatureAccess($data->nomenclature);

            try {
                $import = new Import($data->nomenclature, $data->file ? $data->file->getPathname() : null, $data->charset);

                $this->get('tactician.commandbus')->handle($import);
                $this->addFlash('success', 'flash.admin.nomenclature.import.success');

                return $this->generateUrl('admin_nomenclature_read', ['nomenclature' => $data->nomenclature->getId()]);
            } catch (ImportException $exception) {
                $form->addError($this->get('error_factory')->create('validators.nomenclature.import.error'));
            } catch (MissingKeysException $exception) {
                $form->addError($this->get('error_factory')->create('validators.nomenclature.import.missing_keys'));
            }
        }

        return null;
    }

    /**
     * Assign a nomenclature to an event
     *
     * @param Request       $request
     * @param FormInterface $form
     * @param Assign        $data
     *
     * @return null|string
     */
    private function handleAssign(Request $request, FormInterface $form, Assign $data)
    {
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $data->event);

            /** @var AssignResult $result */
            $result = $this->get('tactician.commandbus')->handle($data);
            $this->addFlash('success', 'flash.admin.nomenclature.assign.success');

            return $this->generateUrl('admin_nomenclature_read', ['nomenclature' => $result->nomenclature->getId()]);
        }

        return null;
    }

    /**
     * Exports Nomenclature data to csv
     *
     * @param Request      $request
     * @param Nomenclature $nomenclature
     *
     * @return BinaryFileResponse
     */
    public function exportAction(Request $request, Nomenclature $nomenclature)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $export     = new ExportData($nomenclature);
        $exportForm = $this->createExportForm($export);

        if ($exportForm->handleRequest($request)->isSubmitted() && $exportForm->isValid()) {
            $filepath = sys_get_temp_dir() . '/nomenclature_' . uniqid();
            $file     = $this->get('application.nomenclature.export.csv_exporter')->export($nomenclature, $filepath, $export->charset);
            $filename = sprintf('nomenclature-%s.csv', Transliterator::urlize($nomenclature->getTitle()));
            $response = new BinaryFileResponse($file);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

            return $response;
        }

        throw $this->createNotFoundException();
    }

    /**
     * @param Nomenclature $nomenclature
     */
    private function denyAccessUnlessNomenclatureAccess(Nomenclature $nomenclature)
    {
        if ($nomenclature->getEvent()) {
            $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $nomenclature->getEvent());
        } else {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        }
    }

    /**
     * @param ExportData $export
     *
     * @return Form
     */
    private function createExportForm(ExportData $export)
    {
        return $this->get('form.factory')->createNamed('', ExportType::class, $export, [
            'action' => $this->generateUrl('admin_nomenclature_export', ['nomenclature' => $export->nomenclature->getId()])
        ]);
    }
}
