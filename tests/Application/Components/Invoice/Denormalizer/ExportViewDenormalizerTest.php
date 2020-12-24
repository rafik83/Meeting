<?php

namespace Proximum\Vimeet\Tests\Application\Components\Invoice\Denormalizer;

use IntlDateFormatter;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\BillingInfosViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\ExportViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\VatListViewDenormalizer;
use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Application\View\Invoice\Vat\VatListView;
use Proximum\Vimeet\Application\View\Invoice\Vat\VatView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Configuration;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ExportViewDenormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $sheetInfoGuesserCache = $this->prophesize(SheetInfoGuesserCache::class);
        $balance = $this->prophesize(Balance::class);
        $sheet = $this->prophesize(Sheet::class);
        $invoice = $this->prophesize(Invoice::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

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
        $invoice->getVatRate()->shouldBeCalled()->willReturn(20);

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

        $data['billingInfosView'] = [
            'gender' => 'man',
            'lastname' => 'dupont',
            'firstname' => 'martin',
            'function' => 'Recruiter',
            'phone' => '+33069090909',
            'mobile' => '+33909090909',
            'email' => 'martin.dupont@elao.com',
            'company' => 'elao',
            'street' => '10 rue saint marc',
            'zipcode' => '75002',
            'city' => 'Paris',
            'country' => 'GB',
            'vatNumber' => 'vatNumber',
            'reference' => null,
        ];

        $dateFormatter = IntlDateFormatter::create(
            'fr',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            'Europe/Paris'
        );

        $exportViewDenormalizer = new ExportViewDenormalizer($sheetInfoGuesserCache->reveal(), $balance->reveal());
        $serializer = new Serializer(
            [
                new BillingInfosViewDenormalizer(),
                new VatListViewDenormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );
        $exportViewDenormalizer->setDenormalizer($serializer);

        $result = $exportViewDenormalizer->denormalize(
            $data,
            ExportView::class,
            'json',
            [
                'locale' => 'fr',
                'dateFormatter' => $dateFormatter,
                'invoice' => $invoice->reveal(),
                'billingInfosViewForSheet' => $billingInfoView,
            ]
        );

        $expectedExportView = new ExportView(
            1,
            'eventTitle',
            88,
            22,
            'sheetTitle',
            'invoiceNumber',
            20,
            '23/06/2016',
            500,
            1000,
            700,
            0,
            'code',
            'vatNumber',
            'GB',
            null
        );

        $this->assertEquals($expectedExportView, $result);
    }

    public function testDenormalizeWithVatListView()
    {
        $sheetInfoGuesserCache = $this->prophesize(SheetInfoGuesserCache::class);
        $balance = $this->prophesize(Balance::class);
        $sheet = $this->prophesize(Sheet::class);
        $invoice = $this->prophesize(Invoice::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

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
        $invoice->getVatRate()->shouldBeCalled()->willReturn(10);

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

        $data['billingInfosView'] = [
            'gender' => 'man',
            'lastname' => 'dupont',
            'firstname' => 'martin',
            'function' => 'Recruiter',
            'phone' => '+33069090909',
            'mobile' => '+33909090909',
            'email' => 'martin.dupont@elao.com',
            'company' => 'elao',
            'street' => '10 rue saint marc',
            'zipcode' => '75002',
            'city' => 'Paris',
            'country' => 'GB',
            'vatNumber' => 'vatNumber',
            'reference' => null,
        ];

        $data['vatListView'] = [
            'vatAmount' => 42800,
            'total' => 3967800,
            'totalWithVat' => 4010600,
            'vatApplicable' => true,
            'vatMode' => 'et',
            'vatViews' => [
                'vat_10' => [
                    'vatRate' => 10,
                    'vatMode' => 'et',
                    'total' => 416000,
                    'totalVat' => 41600,
                ],
                'vat_0' => [
                    'vatRate' => 0,
                    'vatMode' => 'et',
                    'total' => 3527800,
                    'totalVat' => 0,
                ],
                'vat_5' => [
                    'vatRate' => 5,
                    'vatMode' => 'et',
                    'total' => 24000,
                    'totalVat' => 1200,
                ],
            ],
        ];

        $dateFormatter = IntlDateFormatter::create(
            'fr',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            'Europe/Paris'
        );

        $exportViewDenormalizer = new ExportViewDenormalizer($sheetInfoGuesserCache->reveal(), $balance->reveal());
        $serializer = new Serializer(
            [
                new BillingInfosViewDenormalizer(),
                new VatListViewDenormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );
        $exportViewDenormalizer->setDenormalizer($serializer);

        $result = $exportViewDenormalizer->denormalize(
            $data,
            ExportView::class,
            'json',
            [
                'locale' => 'fr',
                'dateFormatter' => $dateFormatter,
                'invoice' => $invoice->reveal(),
                'billingInfosViewForSheet' => $billingInfoView,
            ]
        );

        $expectedExportView = new ExportView(
            1,
            'eventTitle',
            88,
            22,
            'sheetTitle',
            'invoiceNumber',
            10,
            '23/06/2016',
            500,
            1000,
            700,
            0,
            'code',
            'vatNumber',
            'GB',
            new VatListView(
                3967800,
                4010600,
                true,
                'et',
                [
                    new VatView(10, 'et', 416000, 41600),
                    new VatView(0, 'et', 3527800, 0),
                    new VatView(5, 'et', 24000, 1200),
                ]
            )
        );

        $this->assertEquals($expectedExportView, $result);
    }
}
