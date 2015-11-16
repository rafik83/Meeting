<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Sheet\Apply;

use Proximum\Vimeet\Application\Components\Sheet\Apply\Applier;
use Proximum\Vimeet\Application\Components\Sheet\Apply\Strategy\SetNullStrategy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\See;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class ApplierTest extends \PHPUnit_Framework_TestCase
{
    public function testApplySetNull()
    {
        $data = [
            '563cb103926e5' => [
                '563cb103926e6' => 'Thomas',
                '563cb10a524c5' => '786, place de Valentin 60 573 Leduc',
                '563cb10fcc9fe' => 'http://charles.net/totam-nulla-quam-ipsam-voluptatem-cupiditate-sed-natus-debitis',
                '563cb115bbeb9' => 'Repellendus illo veritatis qui ex. Veritatis voluptate vel possimus omnis aut.',
            ],
            '563cb11d08df0' => [
                '563cb11d08df1' => 'Qui cupiditate eos quod veritatis vel optio provident non.',
            ],
        ];

        $rule = [
            'sheet' => [
                '563cb103926e5' => [
                    '563cb103926e6' => true,
                    '563cb10a524c5' => true,
                    '563cb10fcc9fe' => true,
                    '563cb115bbeb9' => false,
                ],
                '563cb11d08df0' => [
                    '563cb11d08df1' => false,
                ],
            ],
        ];

        $expectedData = [
            '563cb103926e5' => [
                '563cb103926e6' => null,
                '563cb10a524c5' => null,
                '563cb10fcc9fe' => null,
                '563cb115bbeb9' => 'Repellendus illo veritatis qui ex. Veritatis voluptate vel possimus omnis aut.',
            ],
            '563cb11d08df0' => [
                '563cb11d08df1' => 'Qui cupiditate eos quod veritatis vel optio provident non.',
            ],
        ];

        $event = new Event();
        $sheet = new Sheet($event, new Type($event), $data, []);
        $see   = new See($event, new Type($event), $sheet->getType(), $rule);

        $applier = new Applier();
        $applier->apply($see, $sheet, new SetNullStrategy());

        $this->assertEquals($expectedData, $sheet->getData());
    }
}
