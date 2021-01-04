<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\InvoiceFactory;

class InvoiceManager
{
    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /** @var SheetManager */
    private $sheetManager;

    /**
     * @param SheetManager               $sheetManager
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        SheetManager $sheetManager,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->sheetManager      = $sheetManager;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param Event      $event
     * @param string     $numero
     * @param Sheet|null $sheet
     *
     * @return Invoice
     */
    public function create(Event $event, $numero, Sheet $sheet = null, ?Order $order = null)
    {
        if (null === $sheet) {
            $sheet = $this->sheetManager->create($event);
        }

        $invoice = InvoiceFactory::create($numero, $sheet);
        if (null !== $order) {
            $order->setInvoice($invoice);
        }
        $this->invoiceRepository->add($invoice);

        return $invoice;
    }
}
