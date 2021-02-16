<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Query\Sheet\CountryViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\CountryViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\CountryView;
use Proximum\Vimeet\Infrastructure\Adapter\IntlAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CountryViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $locale = 'fr';
        $franceCountryCode = 'fr';
        $franceCountryName = 'France';
        $germanyCountryCode = 'de';
        $germanyCountryName = 'Allemagne';

        $expectedCountries = [
            'countryCodes' => [
                'buckets' => [
                    ['key' => $franceCountryCode],
                    ['key' => $germanyCountryCode]
                ]
            ]
        ];

        $sheetSearchAdapter = $this->prophesize(SheetSearchAdapterInterface::class);
        $sheetSearchAdapter->getCountries($event, $locale)->shouldBeCalled()->willReturn($expectedCountries);

        $intlAdapter = $this->prophesize(IntlAdapter::class);
        $intlAdapter->getCountryName($franceCountryCode, $locale)
            ->shouldBeCalled()
            ->willReturn($franceCountryName);
        $intlAdapter->getCountryName($germanyCountryCode, $locale)
            ->shouldBeCalled()
            ->willReturn($germanyCountryName);

        $handler = new CountryViewQueryHandler($sheetSearchAdapter->reveal(), $intlAdapter->reveal());

        $result = $handler->handle(new CountryViewQuery($event, $locale));

        $this->assertCount(2, $result);
        $this->assertInstanceOf(CountryView::class, $result[0]);
        $this->assertEquals($germanyCountryName, $result[0]->name);
        $this->assertEquals($franceCountryCode, $result[1]->code);
    }
}
