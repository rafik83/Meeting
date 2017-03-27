<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Invoice;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Proximum\Vimeet\Application\Command\Invoice\Export;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Invoice\ExportType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Proximum\Vimeet\Application\Serializer\Charset;

class ExportController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request, Admin $admin)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $response = new Response();
        $export   = new Export($admin);
        $form     = $this->createForm(ExportType::class, $export)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $charset        = Charset::WINDOWS_1252;
            $invoicesNormaliserView = $this->get('tactician.commandbus')->handle($export);

            $serializer    = $this->get('serializer');
            $exportContent = $serializer->serialize($invoicesNormaliserView, 'csv', [
                'locale'  => $request->getLocale(),
                'charset' => $charset,
            ]);

            $response->setContent($exportContent);
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                "export_invoices_" . date("Y_m_d_His") . ".csv"
            );
            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));
        }

        return $response;
    }

}
