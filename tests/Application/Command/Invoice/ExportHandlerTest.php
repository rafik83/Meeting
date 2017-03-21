<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Invoice;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Invoice\Export;
use Proximum\Vimeet\Application\Command\Invoice\ExportHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Proximum\Vimeet\Domain\View\Normalizer\InvoicesNormalizerView;

class ExportHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $date  = new \DateTime();

        $admin = $this->prophesize(Admin::class);
        $admin->getId()->shouldBeCalled()->willReturn(22);
        $admin->getLocale()->shouldBeCalled()->willReturn('fr');

        $sheet->getOwner()->shouldBeCalled()->willReturn($admin);

        $export             = new Export($admin->reveal());
        $invoice            = $this->prophesize(Invoice::class);
        $eventConfiguration = new Event\Configuration('leftColor', 'rightColor', 'textColor');
        $eventConfiguration->setAnalyticsCode('code');
        $event->getId()->shouldBeCalled()->willReturn(1);
        $event->getTitle()->shouldBeCalled()->willReturn('eventTitle');
        $event->getConfiguration()->shouldBeCalled()->willReturn($eventConfiguration);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');
        $event->getTimeZone()->shouldBeCalled()->willReturn($date->getTimezone());

        $invoice->getEvent()->shouldBeCalled()->willReturn($event);
        $invoice->getSheet()->shouldBeCalled()->willReturn($sheet);
        $invoice->getNumber()->shouldBeCalled()->willReturn('invoiceNumber');
        $invoice->getTotal()->shouldBeCalled()->willReturn(500);
        $invoice->getTotalWithVat()->shouldBeCalled()->willReturn(1000);
        $invoice->getVatAmount()->shouldBeCalled()->willReturn(700);
        $invoice->getCreatedAt()->shouldBeCalled()->willReturn($date);
        $invoice->getData()->shouldBeCalled()->willReturn([]);

        $serializer        = $this->prophesize(SerializerAdapterInterface::class);
        $eventRepository   = $this->prophesize(EventRepositoryInterface::class);
        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $sheetInfoGuesser  = $this->prophesize(SheetInfoGuesser::class);
        $balance           = $this->prophesize(Balance::class);

        $balance->getBalance($sheet)->shouldBeCalled()->willReturn(643);

        $filters = ['date' => [
            'beginDate' => null,
            'endDate'   => null,
        ]];

        $eventRepository->getEventsByAdmin($admin)->shouldBeCalled()->willReturn([$event]);
        $invoiceRepository->getAllByEvent($event, $filters)->shouldBeCalled()->willReturn([$invoice]);
        $sheetInfoGuesser->guessSheetTitle($sheet, 'fr')->shouldBeCalled()->willReturn('sheetTitle');

        $exportView = new ExportView(
            1,
            'eventTitle',
            22,
            'sheetTitle',
            'invoiceNumber',
            $date->format('d/m/y'),
            500,
            1000,
            700,
            643,
            'code'
        );

        $serializer
            ->deserialize(
                [],
                ExportView::class,
                'json',
                ['invoice' => $exportView])
            ->shouldBeCalled()
            ->willReturn($exportView);

        $expectedInvoicesNormalizerView = new InvoicesNormalizerView([$exportView], 'fr');

        $handler = new ExportHandler(
            $serializer->reveal(),
            $eventRepository->reveal(),
            $invoiceRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $balance->reveal()
        );

        $result = $handler->handle($export);

        $this->assertEquals($expectedInvoicesNormalizerView->exportViews[0], $result->exportViews[0]);
    }
}
