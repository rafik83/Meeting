<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Query\Sheet\CountryViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\CountryViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\CountryView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CountryViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $locale = 'fr';
        $expectedCountries = [
            'countries_aggs' => [
                'countries' => [
                    'countries_filter' => [
                        'countries' => [
                            'buckets' =>
                            [
                                'key' => 'france',
                            ],
                            [
                                'key' => 'allemagne'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $sheetSearchAdapter = $this->prophesize(SheetSearchAdapterInterface::class);
        $sheetSearchAdapter->getCountries($event, $locale)->shouldBeCalled()->willReturn($expectedCountries);

        $handler = new CountryViewQueryHandler($sheetSearchAdapter->reveal());

        $result = $handler->handle(new CountryViewQuery($event, $locale));

        $this->assertCount(2, $result);
        $this->assertInstanceOf(CountryView::class, $result[0]);
        $this->assertEquals('allemagne', $result[1]->name);
    }
}
