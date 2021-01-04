<?php

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Application\Exception\Invoice\InvalidNumeroInvoiceException;
use Proximum\Vimeet\Application\Exception\Invoice\InvoiceNotFoundException;
use Proximum\Vimeet\Application\Exception\Invoice\IsNotAllowedToFindInvoiceException;
use Proximum\Vimeet\Domain\Exception\Invoice\CanNotExplodeNotValidNumeroInvoiceException;
use Proximum\Vimeet\Domain\Invoice\Finder;
use Proximum\Vimeet\Domain\Invoice\Numero\Exploder;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;

class FindHandler
{
    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param Find $find
     *
     * @throws InvalidNumeroInvoiceException
     * @throws InvoiceNotFoundException
     * @throws IsNotAllowedToFindInvoiceException
     *
     * @return FindResult
     */
    public function handle(Find $find)
    {
        if (!Finder::isAllowedToFind($find->admin)) {
            throw new IsNotAllowedToFindInvoiceException(
                sprintf('This user of id %s is not allowed to find an invoice', $find->admin->getId())
            );
        }

        $numero = $find->numero;

        try {
            $invoiceNumeroView = Exploder::explode($numero);
        } catch (CanNotExplodeNotValidNumeroInvoiceException $exception) {
            throw new InvalidNumeroInvoiceException(
                sprintf('The given numero %s is not valid', $numero)
            );
        }

        $invoices = $this->invoiceRepository->findByNumero($invoiceNumeroView);

        if (empty($invoices)) {
            throw new InvoiceNotFoundException(
                sprintf('The invoice with numero %s does not exist', $numero)
            );
        }

        $sheets = [];

        foreach ($invoices as $invoice) {
            if (Finder::isAllowedToAccess($find->admin, $invoice)) {
                $sheets[] = $invoice->getSheet();
            }
        }

        if (empty($sheets)) {
            throw new InvoiceNotFoundException(sprintf('The invoice with numero %s does not exist', $numero));
        }

        return new FindResult($sheets);
    }
}
