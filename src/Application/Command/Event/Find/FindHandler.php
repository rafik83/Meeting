<?php

namespace Proximum\Vimeet\Application\Command\Event\Find;

use Proximum\Vimeet\Application\Command\Invoice\Find as InvoiceFind;
use Proximum\Vimeet\Application\Command\Invoice\FindHandler as InvoiceFindHandler;
use Proximum\Vimeet\Application\Command\Invoice\FindResult as InvoiceFindResult;
use Proximum\Vimeet\Application\Command\Order\Find as OrderFind;
use Proximum\Vimeet\Application\Command\Order\FindHandler as OrderFindHandler;
use Proximum\Vimeet\Application\Command\Order\FindResult as OrderFindResult;
use Proximum\Vimeet\Application\Exception\Event\InvalidFindException;
use Proximum\Vimeet\Application\Exception\Invoice\InvalidNumeroInvoiceException;
use Proximum\Vimeet\Application\Exception\Invoice\InvoiceNotFoundException;
use Proximum\Vimeet\Application\Exception\Invoice\IsNotAllowedToFindInvoiceException;
use Proximum\Vimeet\Application\Exception\Order\InvalidNumeroOrderException;
use Proximum\Vimeet\Application\Exception\Order\IsNotAllowedToFindOrderException;
use Proximum\Vimeet\Application\Exception\Order\OrderNotFoundException;

class FindHandler
{
    /** @var OrderFindHandler */
    private $orderFindHandler;

    /** @var InvoiceFindHandler */
    private $invoiceFindHandler;

    /**
     * @param OrderFindHandler   $orderFindHandler
     * @param InvoiceFindHandler $invoiceFindHandler
     */
    public function __construct(OrderFindHandler $orderFindHandler, InvoiceFindHandler $invoiceFindHandler)
    {
        $this->orderFindHandler   = $orderFindHandler;
        $this->invoiceFindHandler = $invoiceFindHandler;
    }

    /**
     * @param Find $find
     *
     * @throws InvalidFindException
     * @throws InvalidNumeroInvoiceException
     * @throws InvoiceNotFoundException
     * @throws IsNotAllowedToFindInvoiceException
     * @throws InvalidNumeroOrderException
     * @throws IsNotAllowedToFindOrderException
     * @throws OrderNotFoundException
     *
     * @return InvoiceFindResult|OrderFindResult
     */
    public function handle(Find $find)
    {
        if ($find->findOrder()) {
            return $this->orderFindHandler->handle(new OrderFind($find->admin, $find->numero));
        } elseif ($find->findInvoice()) {
            return $this->invoiceFindHandler->handle(new InvoiceFind($find->admin, $find->numero));
        }

        throw new InvalidFindException();
    }
}
