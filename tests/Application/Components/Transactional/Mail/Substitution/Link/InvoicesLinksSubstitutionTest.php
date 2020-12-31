<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution\Link;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQuery;
use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQueryHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\Link\InvoicesLinksSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareBatchSheetMailView;
use Proximum\Vimeet\Application\View\Invoice\InvoiceUrlView;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class InvoicesLinksSubstitutionTest extends TestCase
{
    public function test_render_invoices_links_substitution()
    {
        $invoice1 = $this->prophesize(Invoice::class);
        $invoice2 = $this->prophesize(Invoice::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $invoiceRepository
            ->findBySheet($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$invoice1->reveal(), $invoice2->reveal()])
        ;

        $invoiceUrlView1 = new InvoiceUrlView(101, 'INVOICE101', 'https://my.events/invoice/101');
        $invoiceUrlView2 = new InvoiceUrlView(1226, 'INVOICE1226', 'https://my.events/invoice/1226');

        $invoiceUrlViewQueryHandler = $this->prophesize(InvoiceUrlViewQueryHandler::class);
        $invoiceUrlViewQueryHandler
            ->handle(new InvoiceUrlViewQuery($invoice1->reveal()))
            ->shouldBeCalled()
            ->willReturn($invoiceUrlView1)
        ;
        $invoiceUrlViewQueryHandler
            ->handle(new InvoiceUrlViewQuery($invoice2->reveal()))
            ->shouldBeCalled()
            ->willReturn($invoiceUrlView2)
        ;

        $expectedRenderedTemplate = 'Invoices links';

        $templating = $this->prophesize(TemplatingAdapterInterface::class);
        $templating
            ->render(
                'MailBundle:Mail/Invoice:_invoiceLink.html.twig',
                [
                    'invoiceUrlViews' => [$invoiceUrlView1, $invoiceUrlView2],
                    'locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($expectedRenderedTemplate)
        ;

        $invoicesLinksSubstitution = new InvoicesLinksSubstitution(
            $invoiceRepository->reveal(),
            $invoiceUrlViewQueryHandler->reveal(),
            $templating->reveal()
        );
        $this->assertEquals(
            $expectedRenderedTemplate,
            $invoicesLinksSubstitution->substitute(
                new PrepareBatchSheetMailView(
                    $event->reveal(),
                    $user->reveal(),
                    Constant::TRANSACTIONAL_MAIL_KEY_SHEET_INVOICED,
                    'fr',
                    $sheet->reveal()
                )
            )
        );
    }
}
