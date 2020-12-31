<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\TypeDoesNotMatchException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Model\Type;

class TypeConverterTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testConvert(Type $type, ?array $expected)
    {
        $mapping = [
            2 => [
                'CategorieIndividuEvt' => 'VISITEUR',
                'ZL_PROFIL' => 'SALON',
            ],
            3 => [
                'CategorieIndividuEvt' => 'PROSPECT',
                'ZL_PROFIL' => null,
            ],
            4 => [
                'condition' => 'CategorieIndividuEvt !== null',
            ]
        ];

        $converter = new TypeConverter();

        if (null === $expected) {
            $this->expectException(TypeDoesNotMatchException::class);
            $converter->convert($type, $mapping);

            return;
        }

        if (null !== $expected) {
            $this->assertEquals($expected, $converter->convert($type, $mapping));
        }
    }

    public function dataProvider(): array
    {
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);
        $type4 = $this->prophesize(Type::class);

        $type1->getId()->willReturn(1);
        $type2->getId()->willReturn(2);
        $type3->getId()->willReturn(3);
        $type4->getId()->willReturn(4);

        return [
            'no-result' => [
                $type1->reveal(),
                null
            ],
            'convertType2' => [
                $type2->reveal(),
                [
                    'CategorieIndividuEvt' => 'VISITEUR',
                    'ZL_PROFIL' => 'SALON',
                ]
            ],
            'mapping-with-null-field' => [
                $type3->reveal(),
                [
                    'CategorieIndividuEvt' => 'PROSPECT',
                    'ZL_PROFIL' => null,
                ]
            ],
            'convertType4' => [
                $type4->reveal(),
                []
            ],
        ];
    }
}
