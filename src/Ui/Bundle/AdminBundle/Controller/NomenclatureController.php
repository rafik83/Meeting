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
use Proximum\Vimeet\Application\Command\Nomenclature\Create;
use Proximum\Vimeet\Application\Command\Nomenclature\CreateResult;
use Proximum\Vimeet\Application\Command\Nomenclature\Import;
use Proximum\Vimeet\Application\Command\Nomenclature\Update;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\ImportException;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\ImportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class NomenclatureController extends Controller
{
    /**
     * List nomenclatures
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $command = new Create();
        $form    = $this->createForm(CreateType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            /** @var CreateResult $result */
            $result = $this->get('tactician.commandbus')->handle($command);

            return $this->redirectToRoute('admin_nomenclature_read', ['nomenclature' => $result->nomenclature->getId()]);
        }

        $nomenclatures = $this->get('repository.nomenclature_repository')->getAll();

        return $this->render('AdminBundle:Nomenclature:list.html.twig', [
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

        // Handle update
        $update     = new Update($nomenclature);
        $updateForm = $this->createForm(UpdateType::class, $update, ['submit' => true]);

        if ($response = $this->handleUpdate($request, $updateForm, $update)) {
            return $response;
        }

        // Handle import
        $importForm = $this->createForm(ImportType::class, []);

        if ($response = $this->handleImport($request, $importForm, $nomenclature)) {
            return $response;
        }

        return $this->render('AdminBundle:Nomenclature:read.html.twig', [
            'update_form'  => $updateForm->createView(),
            'import_form'  => $importForm->createView(),
            'nomenclature' => $nomenclature,
        ]);
    }

    /**
     * Handle Nomenclature update
     *
     * @param Request       $request
     * @param FormInterface $updateForm
     * @param Update        $update
     *
     * @return null|RedirectResponse
     */
    private function handleUpdate(Request $request, FormInterface $updateForm, Update $update)
    {
        if ($updateForm->handleRequest($request)->isSubmitted() && $updateForm->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.nomenclature.update.success');

            return $this->redirectToRoute('admin_nomenclature_read', ['nomenclature' => $update->nomenclature->getId()]);
        }

        return null;
    }

    /**
     * Handle csv import of a Nomenclature
     *
     * @param Request       $request
     * @param FormInterface $importForm
     * @param Nomenclature  $nomenclature
     *
     * @return null|RedirectResponse
     */
    private function handleImport(Request $request, FormInterface $importForm, Nomenclature $nomenclature)
    {
        if ($importForm->handleRequest($request)->isSubmitted() && $importForm->isValid()) {

            /** @var UploadedFile $file */
            $file   = $importForm->get('file')->getData();
            $import = new Import($nomenclature, $file ? $file->getPathname() : null);

            try {
                $this->get('tactician.commandbus')->handle($import);
                $this->addFlash('success', 'flash.admin.nomenclature.import.success');

                return $this->redirectToRoute('admin_nomenclature_read', ['nomenclature' => $nomenclature->getId()]);
            } catch (ImportException $exception) {
                $importForm->addError($this->get('error_factory')->create('validators.nomenclature.import.error'));
            }
        }

        return null;
    }

    /**
     * Exports Nomenclature data to csv
     *
     * @param Nomenclature $nomenclature
     *
     * @return BinaryFileResponse
     */
    public function exportAction(Nomenclature $nomenclature)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $filepath = sys_get_temp_dir() . '/nomenclature_' . uniqid();
        $file     = $this->get('application.nomenclature.export.csv_exporter')->export($nomenclature, $filepath);
        $filename = sprintf('nomenclature-%s.csv', Transliterator::urlize($nomenclature->getTitle()));
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}
