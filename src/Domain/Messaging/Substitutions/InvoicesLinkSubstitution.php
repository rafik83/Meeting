<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQuery;
use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Twig\Environment;

class InvoicesLinkSubstitution implements SubstituteInterface
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var InvoiceUrlViewQueryHandler
     */
    private $invoiceUrlViewQueryHandler;

    private Environment $twig;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceUrlViewQueryHandler $invoiceUrlViewQueryHandler,
        Environment $twig
    ) {
        $this->invoiceRepository          = $invoiceRepository;
        $this->invoiceUrlViewQueryHandler = $invoiceUrlViewQueryHandler;
        $this->twig                       = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        $invoices = $this->invoiceRepository->findBySheet($sheet);

        $invoiceUrlViews = array_map(
            function (Invoice $invoice) {
                return $this->invoiceUrlViewQueryHandler->handle(new InvoiceUrlViewQuery($invoice));
            },
            $invoices
        );

        $links = $this->twig
            ->load('MailBundle:Mail/Invoice:_invoiceLink.html.twig')
            ->render(['invoiceUrlViews' => $invoiceUrlViews, 'locale' => $locale]);

        return $links;
    }
}
