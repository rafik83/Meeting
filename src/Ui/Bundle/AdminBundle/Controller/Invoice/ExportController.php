<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Invoice;

use Proximum\Vimeet\Application\Command\Invoice\Export;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Invoice\ExportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends Controller
{
    /**
     * @param UserInterface $admin
     * @param Request       $request
     *
     * @return Response
     */
    public function exportAction(UserInterface $admin, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $export = new Export($admin);
        $form   = $this->createForm(ExportType::class, $export, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $invoicesNormaliserView = $this->get('tactician.commandbus')->handle($export);

                $exportContent = $this->get('serializer')->serialize($invoicesNormaliserView, 'csv', [
                    'locale'        => $request->getLocale(),
                    'charset'       => Charset::WINDOWS_1252,
                    'csv_delimiter' => ';',
                ]);

                return new CsvFileResponse($exportContent, 'export_invoices_' . date('Y_m_d_His') . '.csv');
            } else {
                $this->addFlash('error', 'flash.admin.invoice.export.failed');
            }
        }

        return $this->render('AdminBundle:Invoice:export.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
