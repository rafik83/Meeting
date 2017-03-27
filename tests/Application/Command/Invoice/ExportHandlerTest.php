<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Invoice;

use IntlDateFormatter;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Invoice\Export;
use Proximum\Vimeet\Application\Command\Invoice\ExportHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;

class ExportHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $date  = new \DateTime('2016-06-23 12:00:00');

        $admin = $this->prophesize(Admin::class);
        $admin->getLocale()->shouldBeCalled()->willReturn('fr');

        $export            = new Export($admin->reveal());
        $export->beginDate = $date;
        $export->endDate   = $date;

        $invoice = $this->prophesize(Invoice::class);
        $invoice->getData()->shouldBeCalled()->willReturn('');
        $invoice->getEvent()->shouldBeCalled()->willReturn($event);

        $serializer        = $this->prophesize(SerializerAdapterInterface::class);
        $eventRepository   = $this->prophesize(EventRepositoryInterface::class);
        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);

        $eventRepository->getEventsByAdmin($admin)->shouldBeCalled()->willReturn([$event]);
        $invoiceRepository->getFilteredByEvents([$event], $date, $date)->shouldBeCalled()->willReturn([$invoice]);

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
                ['invoice' => $invoice, 'locale' => 'fr', 'dateFormatter' => $dateFormatter])
            ->shouldBeCalled()
        ;

        $handler = new ExportHandler(
            $serializer->reveal(),
            $eventRepository->reveal(),
            $invoiceRepository->reveal()
        );

        $handler->handle($export);
    }
}
