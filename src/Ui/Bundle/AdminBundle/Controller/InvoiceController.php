<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\InvoicePrefix\Create;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceExportQuery;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\InvoicePrefix\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Proximum\Vimeet\Application\Serializer\Charset;

class InvoiceController extends Controller
{
    /**
     * @return Response
     *
     */
    public function listAction()
    {
        return $this->render('AdminBundle:Invoice:list.html.twig', [
            'list' => $this->get('repository.invoice.prefix_repository')->getAll(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $create = new Create();

        $form = $this->createForm(CreateType::class, $create);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.invoice.create.success');

            return $this->redirectToRoute('admin_invoice_globals_list');
        }

        return $this->render('AdminBundle:Invoice:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $charset        = Charset::WINDOWS_1252;
        $normaliserView = $this->get('tactician.commandbus.query')->handle(new InvoiceExportQuery($this->getUser()));

        $serializer    = $this->get('serializer');
        $exportContent = $serializer->serialize($normaliserView, 'csv', [
            'locale'  => $request->getLocale(),
            'charset' => $charset,
        ]);

        $response    = new Response($exportContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "export_invoices_" . date("Y_m_d_His") . ".csv"
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));

        return $response;
    }
}
