<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\DataConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\MainDataConverter;
use Proximum\Vimeet\Domain\Model\Event;

class DataConverterTest extends TestCase
{
    public function testConvert()
    {
        $rawData = ['Prenom' => 'Bruce', 'Nom' => 'Willis'];

        $expectedResult = [
            'participant_firstname' => 'Bruce',
            'participant_lastname' => 'Willis',
        ];

        $event = $this->prophesize(Event::class);
        $mainDataConverter = $this->prophesize(MainDataConverter::class);
        $mainDataConverter
            ->convert($rawData)
            ->shouldBeCalled()
            ->willReturn(
                [
                    'participant_firstname' => 'Bruce',
                    'participant_lastname' => 'Willis',
                ]
            )
        ;

        $dataConverter = new DataConverter($mainDataConverter->reveal());
        $this->assertEquals($expectedResult, $dataConverter->convert($event->reveal(), $rawData));
    }
}
