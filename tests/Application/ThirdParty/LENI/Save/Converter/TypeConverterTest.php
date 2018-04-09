<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Model\Type;

class TypeConverterTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testConvert(Type $type, array $mapping, array $expected)
    {
        $converter = new TypeConverter();
        $result = $converter->convert($type, $mapping);

        $this->assertEquals($expected, $result);
    }

    public function dataProvider(): array
    {
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);

        $type1->getId()->willReturn(1);
        $type2->getId()->willReturn(2);
        $type3->getId()->willReturn(3);

        $mapping = [
            2 => [
                'CategorieIndividuEvt' => 'VISITEUR',
                'ZL_PROFIL' => 'SALON',
            ],
            3 => [
                'CategorieIndividuEvt' => 'PROSPECT',
                'ZL_PROFIL' => null,
            ]
        ];

        return [
            'no-result' => [
                $type1->reveal(),
                $mapping,
                []
            ],
            [
                $type2->reveal(),
                $mapping,
                [
                    'CategorieIndividuEvt' => 'VISITEUR',
                    'ZL_PROFIL' => 'SALON',
                ]
            ],
            'mapping-with-null-field' => [
                $type3->reveal(),
                $mapping,
                [
                    'CategorieIndividuEvt' => 'PROSPECT',
                    'ZL_PROFIL' => null,
                ]
            ],
        ];
    }
}
