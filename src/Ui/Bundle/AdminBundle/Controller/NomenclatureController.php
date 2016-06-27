<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Nomenclature\Create;
use Proximum\Vimeet\Application\Command\Nomenclature\CreateResult;
use Proximum\Vimeet\Application\Command\Nomenclature\Import;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\ImportException;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\ImportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NomenclatureController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
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
     * @param Request      $request
     * @param Nomenclature $nomenclature
     *
     * @return RedirectResponse|Response
     */
    public function readAction(Request $request, Nomenclature $nomenclature)
    {
        $form = $this->createForm(ImportType::class, [], ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file    = $form->get('file')->getData();
            $command = new Import($nomenclature, $file->getPathname());

            try {
                $this->get('tactician.commandbus')->handle($command);

                return $this->redirectToRoute('admin_nomenclature_read', ['nomenclature' => $nomenclature->getId()]);
            } catch (ImportException $exception) {
                $form->addError($this->get('error_factory')->create('validators.nomenclature.import.error'));
            }
        }

        return $this->render('AdminBundle:Nomenclature:read.html.twig', [
            'form'         => $form->createView(),
            'nomenclature' => $nomenclature,
        ]);
    }
}
