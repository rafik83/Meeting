<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link;

use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQuery;
use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQueryHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstituteInterface;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;

class InvoicesLinksSubstitution implements SubstituteInterface
{
    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /** @var InvoiceUrlViewQueryHandler */
    private $invoiceUrlViewQueryHandler;

    /** @var TemplatingAdapterInterface */
    private $templating;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceUrlViewQueryHandler $invoiceUrlViewQueryHandler,
        TemplatingAdapterInterface $templating
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceUrlViewQueryHandler = $invoiceUrlViewQueryHandler;
        $this->templating = $templating;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        $invoices = $this->invoiceRepository->findBySheet($prepareMail->sheet);

        $invoiceUrlViews = array_map(
            function (Invoice $invoice) {
                return $this->invoiceUrlViewQueryHandler->handle(new InvoiceUrlViewQuery($invoice));
            },
            $invoices
        );

        return $this->templating->render(
            'MailBundle:Mail/Invoice:_invoiceLink.html.twig',
            [
                'invoiceUrlViews' => $invoiceUrlViews,
                'locale' => $prepareMail->locale,
            ]
        );
    }
}
