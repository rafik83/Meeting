<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Order\Export\SharedColumnsTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\SharedColumnsTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\SharedColumnsTranslationView;

class SharedColumnsTranslationViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $adminLocale = 'fr';
        $translator = $this->prophesize(TranslatorInterface::class);
        $translator->trans('order.column.order_id', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Order id');
        $translator->trans('order.column.order_date', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Date de la commande');
        $translator->trans('order.column.sheet_id', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Sheet id');
        $translator->trans('order.column.sheet_title', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Sheet title');
        $translator->trans('order.column.invoice_number', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Invoice number');
        $translator->trans('order.column.invoice_date', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('12/10/2017');
        $translator->trans('order.column.billing_info_gender', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Gender');
        $translator->trans('order.column.billing_info_last_name', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Last name');
        $translator->trans('order.column.billing_info_first_name', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('first name');
        $translator->trans('order.column.billing_info_position', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Position');
        $translator->trans('order.column.billing_info_phone', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Phone');
        $translator->trans('order.column.billing_info_mobile', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Mobile');
        $translator->trans('order.column.billing_info_email', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Email');
        $translator->trans('order.column.billing_info_company', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Company');
        $translator->trans('order.column.billing_info_street', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Street');
        $translator->trans('order.column.billing_info_zip_code', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Zip code');
        $translator->trans('order.column.billing_info_city', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('City');
        $translator->trans('order.column.billing_info_country', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Country');
        $translator->trans('order.column.billing_info_vat_number', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Vat Number');
        $translator->trans('order.column.billing_info_reference', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Reference');
        $translator->trans('order.column.order_total_without_vat', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Total without vat');
        $translator->trans('order.column.order_total_vat', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Total vat');
        $translator->trans('order.column.order_total_with_vat', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Total with vat');

        $handler = new SharedColumnsTranslationViewQueryHandler($translator->reveal());
        $result = $handler->handle(new SharedColumnsTranslationViewQuery($adminLocale));

        $expected = new SharedColumnsTranslationView(
            'Order id',
            'Date de la commande',
            'Sheet id',
            'Sheet title',
            'Invoice number',
            '12/10/2017',
            'Gender',
            'Last name',
            'first name',
            'Position',
            'Phone',
            'Mobile',
            'Email',
            'Company',
            'Street',
            'Zip code',
            'City',
            'Country',
            'Vat Number',
            'Reference',
            'Total without vat',
            'Total vat',
            'Total with vat'
        );

        $this->assertEquals($expected, $result);
    }
}
