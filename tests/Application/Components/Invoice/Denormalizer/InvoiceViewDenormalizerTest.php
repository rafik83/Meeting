<?php

namespace Proximum\Vimeet\Tests\Application\Components\Invoice\Denormalizer;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\BillingInfosViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\GroupsViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\GroupViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\InvoiceViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\PromotionCodesViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\PromotionCodeViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\PromotionProductRowViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\RowViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\SummaryViewDenormalizer;
use Proximum\Vimeet\Application\Components\Invoice\Denormalizer\VatListViewDenormalizer;
use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Application\View\Invoice\CustomRowView;
use Proximum\Vimeet\Application\View\Invoice\GroupsView;
use Proximum\Vimeet\Application\View\Invoice\GroupView;
use Proximum\Vimeet\Application\View\Invoice\IncludedProductView;
use Proximum\Vimeet\Application\View\Invoice\InvoiceView;
use Proximum\Vimeet\Application\View\Invoice\PromotionCodesView;
use Proximum\Vimeet\Application\View\Invoice\PromotionCodeView;
use Proximum\Vimeet\Application\View\Invoice\PromotionProductRowView;
use Proximum\Vimeet\Application\View\Invoice\RowView;
use Proximum\Vimeet\Application\View\Invoice\SummaryView;
use Proximum\Vimeet\Application\View\Invoice\Vat\VatListView;
use Proximum\Vimeet\Application\View\Invoice\Vat\VatView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * WARNING:
 * This test ensure that Invoice data are always deserialized in the right way
 * It should never changed because it is an extract of real invoice
 * If the code should changed, this test must always pass without changes
 */
class InvoiceViewDenormalizerTest extends TestCase
{
    public function testDenormalizeInvoiceView()
    {
        $event  = EventFactory::createEvent('My Event', 'fr');
        $sheet  = SheetFactory::create($event);
        $prefix = new Prefix('Vimeet', 'Vi');
        $date   = new \DateTime('2016-12-25 08:00:00');

        /**
         * This json data must not changes because it is an extract of real invoice data
         */
        $data = "{\"summaryView\":{\"groups\":{\"groups\":[{\"groupId\":null,\"label\":\"Exposant - stand 4m\\u00b2\",\"type\":\"plan\",\"products\":[{\"label\":\"Exposant - stand 4m\\u00b2\",\"quantity\":1,\"price\":265000,\"total\":265000,\"productId\":10,\"customRows\":[{\"label\":\"4m\\u00b2 suppl\\u00e9mentaires - remis\\u00e9\",\"quantity\":1,\"price\":50000,\"total\":50000}],\"includedProducts\":[{\"label\":\"Participant inclus\",\"quantity\":1,\"price\":0,\"total\":0},{\"label\":\"Pack de rendez-vous\",\"quantity\":1,\"price\":0,\"total\":0}]}],\"customRows\":[]},{\"groupId\":null,\"label\":\"Participant suppl\\u00e9mentaire\",\"type\":\"participant\",\"products\":[{\"label\":\"Participant suppl\\u00e9mentaire\",\"quantity\":3,\"price\":9500,\"total\":28500,\"productId\":34,\"customRows\":[],\"includedProducts\":[]}],\"customRows\":[]},{\"groupId\":5,\"label\":\"Communication\",\"type\":\"option\",\"products\":[{\"label\":\"LOGO sur votre fiche de pr\\u00e9sentation\",\"quantity\":1,\"price\":12500,\"total\":12500,\"productId\":4,\"customRows\":[{\"label\":\"Custom row for a product\",\"quantity\":1,\"price\":-5000,\"total\":-5000}],\"includedProducts\":[]}],\"customRows\":[]},{\"groupId\":6,\"label\":\"Sous-\\u00e9v\\u00e9nements\",\"type\":\"option\",\"products\":[],\"customRows\":[{\"label\":\"Custom row for a group\",\"quantity\":2,\"price\":9900,\"total\":19800}]},{\"groupId\":7,\"label\":\"Mobiliers suppl\\u00e9mentaires\",\"type\":\"option\",\"products\":[],\"customRows\":[]}]},\"promotionCodes\":{\"promotionCodes\":[{\"label\":\"Offre sp\\u00e9ciale\",\"description\":\"- 2 participants offerts - Logo offert\",\"quantity\":1,\"total\":-31500,\"promotionProductRowViews\":[{\"product\":\"LOGO\",\"promotionType\":\"free\",\"discountValue\":0,\"quantity\":1},{\"product\":\"Participant suppl\\u00e9mentaire - PO\\\/SR\",\"promotionType\":\"free\",\"discountValue\":0,\"quantity\":3}]}]},\"vatMode\":\"et\",\"currency\":\"EUR\"},\"billingInfosView\":{\"gender\":\"man\",\"lastname\":\"DUPOND\",\"firstname\":\"Laurent\",\"function\":\"Directeur\",\"phone\":\"+33122334455\",\"mobile\":\"+33611223344\",\"email\":\"my-email@example.net\",\"company\":\"My company\",\"vatNumber\":\"My vat number\",\"street\":\"10 rue Saint Marc\",\"zipcode\":\"75002\",\"city\":\"Paris\",\"country\":\"FR\",\"reference\":\"My reference\"},\"amountRemainToPay\":17760}";

        $invoice = new Invoice(
            $event,
            $sheet,
            $prefix,
            'Vi',
            2016,
            7,
            true,
            Event::VAT_MODE_ET,
            20,
            339300,
            407160,
            67860,
            'EUR',
            $data,
            $date
        );

        $billingInfosViewOfSheet = new BillingInfosView(
            'man',
            'DUPOND',
            'Laurent',
            'Directeur',
            '+33122334455',
            '+33611223344',
            'my-email@example.net',
            'My company',
            '10 rue Saint Marc',
            '75002',
            'Paris',
            'EN',
            'My vat changed number',
            'My reference'
        );

        $serializer = new Serializer(
            [
                new InvoiceViewDenormalizer(),
                new SummaryViewDenormalizer(),
                new BillingInfosViewDenormalizer(),
                new GroupsViewDenormalizer(),
                new GroupViewDenormalizer(),
                new RowViewDenormalizer(),
                new PromotionCodesViewDenormalizer(),
                new PromotionCodeViewDenormalizer(),
                new PromotionProductRowViewDenormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );

        $invoiceView = $serializer->deserialize(
            $invoice->getData(),
            InvoiceView::class,
            'json',
            [
                'invoice'                 => $invoice,
                'billingInfosViewOfSheet' => $billingInfosViewOfSheet,
            ]
        );

        /**
         * This InvoiceView should never changed because it is the representation of Invoice data
         */
        $expectedInvoiceView = new InvoiceView(
            'Vi2016-0007',
            true,
            'et',
            20,
            339300,
            407160,
            67860,
            'EUR',
            'My Event',
            null,
            $date,
            'fr',
            $event->getTimeZone(),
            '',
            '',
            '',
            '',
            new SummaryView(
                new GroupsView(
                    [
                        new GroupView(
                            'Exposant - stand 4m²',
                            'plan',
                            null,
                            [
                                new RowView(
                                    'Exposant - stand 4m²',
                                    1,
                                    265000,
                                    265000,
                                    10,
                                    [
                                        new CustomRowView(
                                            '4m² supplémentaires - remisé',
                                            50000,
                                            1,
                                            50000
                                        ),
                                    ],
                                    [
                                        new IncludedProductView(
                                            'Participant inclus',
                                            1,
                                            0,
                                            0
                                        ),
                                        new IncludedProductView(
                                            'Pack de rendez-vous',
                                            1,
                                            0,
                                            0
                                        ),
                                    ]
                                ),
                            ],
                            []
                        ),
                        new GroupView(
                            'Participant supplémentaire',
                            'participant',
                            null,
                            [
                                new RowView(
                                    'Participant supplémentaire',
                                    3,
                                    9500,
                                    28500,
                                    34
                                ),
                            ]
                        ),
                        new GroupView(
                            'Communication',
                            'option',
                            5,
                            [
                                new RowView(
                                    'LOGO sur votre fiche de présentation',
                                    1,
                                    12500,
                                    12500,
                                    4,
                                    [
                                        new CustomRowView(
                                            'Custom row for a product',
                                            -5000,
                                            1,
                                            -5000
                                        ),
                                    ]
                                ),
                            ]
                        ),
                        new GroupView(
                            'Sous-événements',
                            'option',
                            6,
                            [],
                            [
                                new CustomRowView(
                                    'Custom row for a group',
                                    9900,
                                    2,
                                    19800
                                ),
                            ]
                        ),
                    ]
                ),
                new PromotionCodesView(
                    [
                        new PromotionCodeView(
                            'Offre spéciale',
                            '- 2 participants offerts - Logo offert',
                            -31500,
                            1,
                            [
                                new PromotionProductRowView(
                                    'LOGO',
                                    'free',
                                    0,
                                    1
                                ),
                                new PromotionProductRowView(
                                    'Participant supplémentaire - PO\/SR',
                                    'free',
                                    0,
                                    3
                                ),
                            ]
                        ),
                    ]
                ),
                'et',
                'EUR'
            ),
            new BillingInfosView(
                'man',
                'DUPOND',
                'Laurent',
                'Directeur',
                '+33122334455',
                '+33611223344',
                'my-email@example.net',
                'My company',
                '10 rue Saint Marc',
                '75002',
                'Paris',
                'FR',
                'My vat number',
                'My reference'
            ),
            null,
            17760
        );

        $this->assertEquals($expectedInvoiceView, $invoiceView);
    }

    public function testDenormalizeInvoiceViewWithVatListView()
    {
        $event  = EventFactory::createEvent('My Event', 'fr');
        $sheet  = SheetFactory::create($event);
        $prefix = new Prefix('Vimeet', 'Vi');
        $date   = new \DateTime('2016-12-25 08:00:00');

        /**
         * This json data must not changes because it is an extract of real invoice data
         */
        $data = '{"summaryView":{"groups":{"groups":[{"groupId":null,"label":"Full entry ticket","type":"plan","products":[{"label":"Full entry ticket","quantity":1,"price":35000,"total":35000,"productId":1668,"vatRate":25,"customRows":[],"includedProducts":[]}],"customRows":[]},{"groupId":null,"label":"Participant 2 days with dinner","type":"participant","products":[{"label":"Participant 2 days with dinner","quantity":1,"price":9900,"total":9900,"productId":1662,"vatRate":25,"customRows":[],"includedProducts":[]}],"customRows":[]},{"groupId":null,"label":"A business meetings planning is aleady included in your registration package","type":"planning","products":[{"label":"A business meetings planning is aleady included in your registration package","quantity":1,"price":0,"total":0,"productId":1512,"vatRate":20,"customRows":[],"includedProducts":[]}],"customRows":[]},{"groupId":272,"label":"Options","type":"option","products":[{"label":"CHAIR","quantity":1,"price":1900,"total":1900,"productId":1745,"vatRate":5.5,"customRows":[{"label":"Remise exceptionnelle","quantity":1,"price":-230,"total":-230}],"includedProducts":[]}],"customRows":[]}]},"promotionCodes":{"promotionCodes":[]},"vatMode":"et","currency":"EUR"},"billingInfosView":{"gender":"man","lastname":"Philipse","firstname":"Esther","function":"CEO","phone":"+31615012306","mobile":"+31615012306","email":"email-70900@example.net","company":"AERspire","vatNumber":null,"street":"Tesla 1","zipcode":"6422 RG","city":"Heerlen","country":"NL","reference":null},"vatListView":{"vatAmount":11273,"total":46570,"totalWithVat":57843,"vatApplicable":true,"vatMode":"et","vatViews":{"vat_25":{"vatRate":25,"vatMode":"et","total":44670,"totalVat":11168},"vat_20":{"vatRate":20,"vatMode":"et","total":0,"totalVat":0},"vat_5.5":{"vatRate":5.5,"vatMode":"et","total":1900,"totalVat":105}}},"amountRemainToPay":57843}';

        $invoice = new Invoice(
            $event,
            $sheet,
            $prefix,
            'Vi',
            2016,
            7,
            true,
            Event::VAT_MODE_ET,
            25,
            339300,
            407160,
            67860,
            'EUR',
            $data,
            $date
        );

        $billingInfosViewOfSheet = new BillingInfosView(
            'man',
            'DUPOND',
            'Laurent',
            'Directeur',
            '+33122334455',
            '+33611223344',
            'my-email@example.net',
            'My company',
            '10 rue Saint Marc',
            '75002',
            'Paris',
            'EN',
            'My vat changed number',
            'My reference'
        );

        $serializer = new Serializer(
            [
                new InvoiceViewDenormalizer(),
                new SummaryViewDenormalizer(),
                new BillingInfosViewDenormalizer(),
                new GroupsViewDenormalizer(),
                new GroupViewDenormalizer(),
                new RowViewDenormalizer(),
                new PromotionCodesViewDenormalizer(),
                new PromotionCodeViewDenormalizer(),
                new PromotionProductRowViewDenormalizer(),
                new VatListViewDenormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );

        $invoiceView = $serializer->deserialize(
            $invoice->getData(),
            InvoiceView::class,
            'json',
            [
                'invoice'                 => $invoice,
                'billingInfosViewOfSheet' => $billingInfosViewOfSheet,
            ]
        );

        /**
         * This InvoiceView should never changed because it is the representation of Invoice data
         */
        $expectedInvoiceView = new InvoiceView(
            'Vi2016-0007',
            true,
            'et',
            25,
            339300,
            407160,
            67860,
            'EUR',
            'My Event',
            null,
            $date,
            'fr',
            $event->getTimeZone(),
            '',
            '',
            '',
            '',
            new SummaryView(
                new GroupsView(
                    [
                        new GroupView(
                            'Full entry ticket',
                            'plan',
                            null,
                            [
                                new RowView(
                                    'Full entry ticket',
                                    1,
                                    35000,
                                    35000,
                                    1668
                                ),
                            ],
                            []
                        ),
                        new GroupView(
                            'Participant 2 days with dinner',
                            'participant',
                            null,
                            [
                                new RowView(
                                    'Participant 2 days with dinner',
                                    1,
                                    9900,
                                    9900,
                                    1662
                                ),
                            ]
                        ),
                        new GroupView(
                            'A business meetings planning is aleady included in your registration package',
                            'planning',
                            null,
                            [
                                new RowView(
                                    'A business meetings planning is aleady included in your registration package',
                                    1,
                                    0,
                                    0,
                                    1512
                                ),
                            ]
                        ),
                        new GroupView(
                            'Options',
                            'option',
                            272,
                            [
                                new RowView(
                                    'CHAIR',
                                    1,
                                    1900,
                                    1900,
                                    1745,
                                    [
                                        new CustomRowView(
                                            'Remise exceptionnelle',
                                            -230,
                                            1,
                                            -230
                                        ),
                                    ]
                                ),
                            ]
                        ),
                    ]
                ),
                new PromotionCodesView([]),
                'et',
                'EUR'
            ),
            new BillingInfosView(
                'man',
                'DUPOND',
                'Laurent',
                'Directeur',
                '+33122334455',
                '+33611223344',
                'my-email@example.net',
                'My company',
                '10 rue Saint Marc',
                '75002',
                'Paris',
                'NL',
                null,
                'My reference'
            ),
            new VatListView(
                46570,
                57843,
                true,
                'et',
                [
                    new VatView(25, 'et', 44670, 11168),
                    new VatView(20, 'et', 0, 0),
                    new VatView(5.5, 'et', 1900, 105),
                ]
            ),
            57843
        );

        $this->assertEquals($expectedInvoiceView, $invoiceView);
    }

    public function testDenormalizeInvoiceViewWithVatListViewAndTypePaymentConditions()
    {
        $event  = EventFactory::createEvent('My Event', 'fr');
        $type = $this->prophesize(Type::class);
        $paymentConditions = $this->prophesize(Type\PaymentConditions::class);
        $type->getPaymentConditions()->shouldBeCalled()->willReturn($paymentConditions);
        $paymentConditions->getBillingAddress('fr')->shouldBeCalled()->willReturn('BillingAddress');
        $paymentConditions->getBankInfo('fr')->shouldBeCalled()->willReturn('BankInfo');
        $paymentConditions->getPaymentCondition('fr')->shouldBeCalled()->willReturn('PaymentCondition');
        $paymentConditions->getPaymentFooter('fr')->shouldBeCalled()->willReturn('PaymentFooter');

        $sheet  = SheetFactory::create($event, null, null, $type->reveal());
        $prefix = new Prefix('Vimeet', 'Vi');
        $date   = new \DateTime('2016-12-25 08:00:00');

        /**
         * This json data must not changes because it is an extract of real invoice data
         */
        $data = '{"summaryView":{"groups":{"groups":[{"groupId":null,"label":"Full entry ticket","type":"plan","products":[{"label":"Full entry ticket","quantity":1,"price":35000,"total":35000,"productId":1668,"vatRate":25,"customRows":[],"includedProducts":[]}],"customRows":[]},{"groupId":null,"label":"Participant 2 days with dinner","type":"participant","products":[{"label":"Participant 2 days with dinner","quantity":1,"price":9900,"total":9900,"productId":1662,"vatRate":25,"customRows":[],"includedProducts":[]}],"customRows":[]},{"groupId":null,"label":"A business meetings planning is aleady included in your registration package","type":"planning","products":[{"label":"A business meetings planning is aleady included in your registration package","quantity":1,"price":0,"total":0,"productId":1512,"vatRate":20,"customRows":[],"includedProducts":[]}],"customRows":[]},{"groupId":272,"label":"Options","type":"option","products":[{"label":"CHAIR","quantity":1,"price":1900,"total":1900,"productId":1745,"vatRate":5.5,"customRows":[{"label":"Remise exceptionnelle","quantity":1,"price":-230,"total":-230}],"includedProducts":[]}],"customRows":[]}]},"promotionCodes":{"promotionCodes":[]},"vatMode":"et","currency":"EUR"},"billingInfosView":{"gender":"man","lastname":"Philipse","firstname":"Esther","function":"CEO","phone":"+31615012306","mobile":"+31615012306","email":"email-70900@example.net","company":"AERspire","vatNumber":null,"street":"Tesla 1","zipcode":"6422 RG","city":"Heerlen","country":"NL","reference":null},"vatListView":{"vatAmount":11273,"total":46570,"totalWithVat":57843,"vatApplicable":true,"vatMode":"et","vatViews":{"vat_25":{"vatRate":25,"vatMode":"et","total":44670,"totalVat":11168},"vat_20":{"vatRate":20,"vatMode":"et","total":0,"totalVat":0},"vat_5.5":{"vatRate":5.5,"vatMode":"et","total":1900,"totalVat":105}}},"amountRemainToPay":57843}';

        $invoice = new Invoice(
            $event,
            $sheet,
            $prefix,
            'Vi',
            2016,
            7,
            true,
            Event::VAT_MODE_ET,
            25,
            339300,
            407160,
            67860,
            'EUR',
            $data,
            $date
        );

        $billingInfosViewOfSheet = new BillingInfosView(
            'man',
            'DUPOND',
            'Laurent',
            'Directeur',
            '+33122334455',
            '+33611223344',
            'my-email@example.net',
            'My company',
            '10 rue Saint Marc',
            '75002',
            'Paris',
            'EN',
            'My vat changed number',
            'My reference'
        );

        $serializer = new Serializer(
            [
                new InvoiceViewDenormalizer(),
                new SummaryViewDenormalizer(),
                new BillingInfosViewDenormalizer(),
                new GroupsViewDenormalizer(),
                new GroupViewDenormalizer(),
                new RowViewDenormalizer(),
                new PromotionCodesViewDenormalizer(),
                new PromotionCodeViewDenormalizer(),
                new PromotionProductRowViewDenormalizer(),
                new VatListViewDenormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );

        $invoiceView = $serializer->deserialize(
            $invoice->getData(),
            InvoiceView::class,
            'json',
            [
                'invoice'                 => $invoice,
                'billingInfosViewOfSheet' => $billingInfosViewOfSheet,
            ]
        );

        /**
         * This InvoiceView should never changed because it is the representation of Invoice data
         */
        $expectedInvoiceView = new InvoiceView(
            'Vi2016-0007',
            true,
            'et',
            25,
            339300,
            407160,
            67860,
            'EUR',
            'My Event',
            null,
            $date,
            'fr',
            $event->getTimeZone(),
            'BillingAddress',
            'BankInfo',
            'PaymentCondition',
            'PaymentFooter',
            new SummaryView(
                new GroupsView(
                    [
                        new GroupView(
                            'Full entry ticket',
                            'plan',
                            null,
                            [
                                new RowView(
                                    'Full entry ticket',
                                    1,
                                    35000,
                                    35000,
                                    1668
                                ),
                            ],
                            []
                        ),
                        new GroupView(
                            'Participant 2 days with dinner',
                            'participant',
                            null,
                            [
                                new RowView(
                                    'Participant 2 days with dinner',
                                    1,
                                    9900,
                                    9900,
                                    1662
                                ),
                            ]
                        ),
                        new GroupView(
                            'A business meetings planning is aleady included in your registration package',
                            'planning',
                            null,
                            [
                                new RowView(
                                    'A business meetings planning is aleady included in your registration package',
                                    1,
                                    0,
                                    0,
                                    1512
                                ),
                            ]
                        ),
                        new GroupView(
                            'Options',
                            'option',
                            272,
                            [
                                new RowView(
                                    'CHAIR',
                                    1,
                                    1900,
                                    1900,
                                    1745,
                                    [
                                        new CustomRowView(
                                            'Remise exceptionnelle',
                                            -230,
                                            1,
                                            -230
                                        ),
                                    ]
                                ),
                            ]
                        ),
                    ]
                ),
                new PromotionCodesView([]),
                'et',
                'EUR'
            ),
            new BillingInfosView(
                'man',
                'DUPOND',
                'Laurent',
                'Directeur',
                '+33122334455',
                '+33611223344',
                'my-email@example.net',
                'My company',
                '10 rue Saint Marc',
                '75002',
                'Paris',
                'NL',
                null,
                'My reference'
            ),
            new VatListView(
                46570,
                57843,
                true,
                'et',
                [
                    new VatView(25, 'et', 44670, 11168),
                    new VatView(20, 'et', 0, 0),
                    new VatView(5.5, 'et', 1900, 105),
                ]
            ),
            57843
        );

        $this->assertEquals($expectedInvoiceView, $invoiceView);
    }
}
