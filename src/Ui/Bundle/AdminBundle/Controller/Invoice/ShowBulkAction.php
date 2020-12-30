<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Invoice;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceQuery;
use Proximum\Vimeet\Application\View\Invoice\InvoiceView;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\InvoicesPdfBulkPrinter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class ShowBulkAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /** @var EngineInterface */
    private $engine;

    /** @var InvoicesPdfBulkPrinter */
    private $invoicesPdfBulkPrinter;

    public function __construct(
        QueryBusInterface $queryBus,
        InvoiceRepositoryInterface $invoiceRepository,
        EngineInterface $engine,
        InvoicesPdfBulkPrinter $invoicesPdfBulkPrinter
    ) {
        $this->queryBus = $queryBus;
        $this->invoiceRepository = $invoiceRepository;
        $this->engine = $engine;
        $this->invoicesPdfBulkPrinter = $invoicesPdfBulkPrinter;
    }

    /**
     * @param Request $request
     * @param string  $format 'html'|'pdf'
     *
     * @return Response
     */
    public function __invoke(Request $request, $format)
    {
        $sheetIds = $request->query->get('sheetIds');

        if ('pdf' === $format) {
            return new BinaryFileResponse(
                $this->invoicesPdfBulkPrinter->generate($sheetIds)
            );
        }

        $invoices = $this->invoiceRepository->findBySheetIds($sheetIds);

        /** @var InvoiceView[] $invoiceViews */
        $invoiceViews = [];

        foreach ($invoices as $invoice) {
            $invoiceViews[] = $this->queryBus->handle(new InvoiceQuery($invoice));
        }

        return new Response(
            $this->engine->render(
                'EventBundle:Invoice:show.forBulk.html.twig',
                [
                    'invoiceViews' => $invoiceViews,
                    'sheetIds' => $sheetIds
                ]
            )
        );
    }
}
