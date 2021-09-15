<?php

namespace Proximum\Vimeet\Domain\Repository\Invoice;

use Proximum\Vimeet\Domain\Invoice\Numero\InvoiceNumeroView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Sheet;

interface InvoiceRepositoryInterface
{
    /**
     * @param Invoice $invoice
     */
    public function add(Invoice $invoice);

    public function set(Invoice $invoice): void;

    /**
     * @param Sheet $sheet
     *
     * @return Invoice[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param int[] $sheetIds
     *
     * @return Invoice[]
     */
    public function findBySheetIds(array $sheetIds): array;

    /**
     * Get last generated invoice for given event invoice prefix
     *
     * @param Prefix $prefix
     * @param $year
     *
     * @return null|Invoice
     */
    public function getLastInvoiceForEventPrefix(Prefix $prefix, $year);

    /**
     * Check if given sheet has invoice, return null if not
     *
     * @param Sheet $sheet
     *
     * @return int|null
     */
    public function isSheetInvoiced(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasInvoice(Sheet $sheet);

    /**
     * @param Event[]            $events
     * @param \DateTimeInterface $beginDate
     * @param \DateTimeInterface $endDate
     *
     * @return Invoice[]
     */
    public function getFilteredByEvents(array $events, \DateTimeInterface $beginDate, \DateTimeInterface $endDate);

    /**
     * @param InvoiceNumeroView $invoiceNumeroView
     *
     * @return Invoice[] multiple invoice can have the same numero
     */
    public function findByNumero(InvoiceNumeroView $invoiceNumeroView);
}
