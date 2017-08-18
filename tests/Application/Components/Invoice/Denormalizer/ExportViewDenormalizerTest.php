<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\ExportViewDenormalizer;
use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use IntlDateFormatter;
use Symfony\Component\Serializer\Serializer;
use PHPUnit\Framework\TestCase;

class ExportViewDenormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $sheetInfoGuesserCache = $this->prophesize(SheetInfoGuesserCache::class);
        $balance               = $this->prophesize(Balance::class);
        $sheet                 = $this->prophesize(Sheet::class);
        $invoice               = $this->prophesize(Invoice::class);
        $event                 = $this->prophesize(Event::class);
        $user                  = $this->prophesize(User::class);

        $date = new \DateTime('2016-06-23 12:00:00');

        $eventConfiguration = new Configuration('leftColor', 'rightColor', 'textColor');
        $eventConfiguration->setAnalyticsCode('code');

        $user->getId()->shouldBeCalled()->willReturn(88);
        $sheet->getOwner()->shouldBeCalled()->willReturn($user->reveal());
        $sheet->getId()->shouldBeCalled()->willReturn(22);

        $event->getId()->shouldBeCalled()->willReturn(1);
        $event->getTitle()->shouldBeCalled()->willReturn('eventTitle');
        $event->getConfiguration()->shouldBeCalled()->willReturn($eventConfiguration);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $invoice->getEvent()->shouldBeCalled()->willReturn($event);
        $invoice->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $invoice->getNumber()->shouldBeCalled()->willReturn('invoiceNumber');
        $invoice->getTotal()->shouldBeCalled()->willReturn(500);
        $invoice->getTotalWithVat()->shouldBeCalled()->willReturn(1000);
        $invoice->getVatAmount()->shouldBeCalled()->willReturn(700);
        $invoice->getCreatedAt()->shouldBeCalled()->willReturn($date);

        $balance->getBalance($sheet->reveal())->shouldBeCalled()->willReturn(0);

        $sheetInfoGuesserCache->guessSheetTitle($sheet, 'fr')->shouldBeCalled()->willReturn('sheetTitle');

        $billingInfoView = new BillingInfosView(
            'man',
            'dupont',
            'martin',
            'Recruiter',
            '+33069090909',
            '+33909090909',
            'martin.dupont@elao.com',
            'elao',
            '10 rue saint marc',
            '75002',
            'Paris',
            'FR',
            null,
            null
        );

        $billingInfoViewOut = new BillingInfosView(
            'man',
            'dupont',
            'martin',
            'Recruiter',
            '+33069090909',
            '+33909090909',
            'martin.dupont@elao.com',
            'elao',
            '10 rue saint marc',
            '75002',
            'Paris',
            'GB',
            'vatNumber',
            null
        );

        $data['billingInfosView'] = [
            'gender'    => 'man',
            'lastname'  => 'dupont',
            'firstname' => 'martin',
            'function'  => 'Recruiter',
            'phone'     => '+33069090909',
            'mobile'    => '+33909090909',
            'email'     => 'martin.dupont@elao.com',
            'company'   => 'elao',
            'street'    => '10 rue saint marc',
            'zipcode'   => '75002',
            'city'      => 'Paris',
            'country'   => 'GB',
            'vatNumber' => 'vatNumber',
            'reference' => null,
        ];

        $expectedExportView = new ExportView(
            1,
            'eventTitle',
            88,
            22,
            'sheetTitle',
            'invoiceNumber',
            '23/06/2016',
            5,
            10,
            7,
            0,
            'code',
            'vatNumber',
            'GB'
        );

        $dateFormatter = IntlDateFormatter::create(
            'fr',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            'Europe/Paris'
        );

        $exportViewDenormalizer = new ExportViewDenormalizer($sheetInfoGuesserCache->reveal(), $balance->reveal());

        $context = [
            'locale'                   => 'fr',
            'dateFormatter'            => $dateFormatter,
            'invoice'                  => $invoice->reveal(),
            'billingInfosViewForSheet' => $billingInfoView,
        ];

        $serializer = $this->prophesize(Serializer::class);
        $serializer->willImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $serializer->denormalize($data['billingInfosView'], BillingInfosView::class, 'json', $context)
            ->willReturn($billingInfoViewOut);

        $exportViewDenormalizer->setDenormalizer($serializer->reveal());

        $result = $exportViewDenormalizer->denormalize(
            $data,
            ExportView::class,
            'json',
            $context
        );

        $this->assertEquals($expectedExportView, $result);
    }
}
