<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQuery;
use Proximum\Vimeet\Application\Components\Invoice\InvoiceUrlViewQueryHandler;
use Proximum\Vimeet\Application\View\Invoice\InvoiceUrlView;
use Proximum\Vimeet\Domain\Messaging\Substitutions\InvoicesLinkSubstitution;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Twig_Template;
use Twig_TemplateWrapper;

class InvoicesLinkSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $event          = EventFactory::createEvent('Proximum');
        $sheet          = SheetFactory::create($event);
        $invoice        = $this->prophesize(Invoice::class);
        $invoiceUrlView = $this->prophesize(InvoiceUrlView::class);
        $locale         = 'fr';

        $invoiceRepository          = $this->prophesize(InvoiceRepositoryInterface::class);
        $invoiceUrlViewQueryHandler = $this->prophesize(InvoiceUrlViewQueryHandler::class);
        $twig                       = $this->prophesize(\Twig_Environment::class);
        $twigTemplate               = $this->prophesize(Twig_Template::class);
        $twigTemplateWrapper        = new Twig_TemplateWrapper($twig->reveal(), $twigTemplate->reveal());

        $invoiceRepository->findBySheet($sheet)->shouldBeCalled()->willReturn([$invoice->reveal()]);

        $invoiceUrlViewQueryHandler->handle(new InvoiceUrlViewQuery($invoice->reveal()))
            ->shouldBeCalled()->willReturn($invoiceUrlView);

        $twig->load('MailBundle:Mail/Invoice:_invoiceLink.html.twig')->shouldBeCalled()
            ->willReturn($twigTemplateWrapper);

        $substitution = new InvoicesLinkSubstitution(
            $invoiceRepository->reveal(),
            $invoiceUrlViewQueryHandler->reveal(),
            $twig->reveal()
        );

        $substitution->getValue($sheet, $locale);
    }
}
