<?php

namespace Proximum\Vimeet\Tests\Application\Command\Invoice;

use IntlDateFormatter;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Invoice\Export;
use Proximum\Vimeet\Application\Command\Invoice\ExportHandler;
use Proximum\Vimeet\Application\Query\Invoice\BillingInfos\BillingInfosQuery;
use Proximum\Vimeet\Application\Query\Invoice\BillingInfos\BillingInfosQueryHandler;
use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;

class ExportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(33);
        $event->getTimeZone()->willReturn('Europe/Paris');
        $beginDate  = new \DateTime('2016-06-23 12:00:00');
        $endDate  = new \DateTime('2016-06-23 23:59:59.999');

        $admin = $this->prophesize(Admin::class);
        $admin->getLocale()->shouldBeCalled()->willReturn('fr');

        $export            = new Export($admin->reveal());
        $export->beginDate = $beginDate;
        $export->endDate   = $endDate;

        $sheet  = $this->prophesize(Sheet::class);
        $invoice = $this->prophesize(Invoice::class);
        $invoice->getData()->shouldBeCalled()->willReturn('');
        $invoice->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $invoice->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $sheet->getId()->shouldBeCalled()->willReturn(22);

        $serializer        = $this->prophesize(SerializerAdapterInterface::class);
        $eventRepository   = $this->prophesize(EventRepositoryInterface::class);
        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $billingInfoQueryHandler = $this->prophesize(BillingInfosQueryHandler::class);
        $billingInfoView = new BillingInfosView(
            'gender',
            'lastName',
            'firstName',
            'position',
            'phone',
            'mobile',
            'email@test.fr',
            'company',
            'street',
            'zipCode',
            'city',
            'country',
            'vatNumber',
            'reference'
        );
        $billingInfoQueryHandler
            ->handle(new BillingInfosQuery($sheet->reveal()))
            ->shouldBeCalled()
            ->willReturn($billingInfoView)
        ;

        $eventRepository->getEventsByAdmin($admin)->shouldBeCalled()->willReturn([$event]);
        $invoiceRepository->getFilteredByEvents([$event], $beginDate, $endDate)->shouldBeCalled()->willReturn([$invoice]);

        $dateFormatter = IntlDateFormatter::create(
            'fr',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            'Europe/Paris'
        );

        $serializer
            ->deserialize(
                '',
                ExportView::class,
                'json',
                [
                    'invoice'                 => $invoice,
                    'locale'                  => 'fr',
                    'dateFormatter'           => $dateFormatter,
                    'billingInfosViewOfSheet' => $billingInfoView,
                ]
            )
            ->shouldBeCalled()
        ;

        $handler = new ExportHandler(
            $serializer->reveal(),
            $eventRepository->reveal(),
            $invoiceRepository->reveal(),
            $billingInfoQueryHandler->reveal()
        );

        $handler->handle($export);
    }
}
