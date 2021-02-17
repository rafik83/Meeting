<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Invoice;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Invoice\Export;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Invoice\ExportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends AbstractController
{
    private SerializerAdapterInterface $serializer;
    private CommandBusInterface $commandBus;

    public function __construct(
        SerializerAdapterInterface $serializer,
        CommandBusInterface $commandBus
    ) {
        $this->serializer = $serializer;
        $this->commandBus = $commandBus;
    }

    public function exportAction(AdminDomain $adminDomain, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $export = new Export($adminDomain->getAdmin());
        $form   = $this->createForm(ExportType::class, $export, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $invoicesNormaliserView = $this->commandBus->handle($export);

                $exportContent = $this->serializer->serialize($invoicesNormaliserView, 'csv', [
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
