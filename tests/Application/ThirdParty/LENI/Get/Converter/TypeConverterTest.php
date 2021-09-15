<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Infrastructure\Adapter\ExpressionLanguageAdapter;

class TypeConverterTest extends TestCase
{
    private $typeConverter;

    public function setUp(): void
    {
        $expressionLanguage = new ExpressionLanguageAdapter();
        $this->typeConverter = new TypeConverter($expressionLanguage);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testConvert(array $payload, ?int $typeId)
    {
        $type1337 = $this->prophesize(Type::class);
        $type1337->getId()->willReturn(1337);

        $type9966 = $this->prophesize(Type::class);
        $type9966->getId()->willReturn(9966);

        $type654 = $this->prophesize(Type::class);
        $type654->getId()->willReturn(654);

        $type1324 = $this->prophesize(Type::class);
        $type1324->getId()->willReturn(1324);

        $typesById = [
            1337 => $type1337->reveal(),
            9966 => $type9966->reveal(),
            654 => $type654->reveal(),
            1324 => $type1324->reveal(),
        ];

        $mapping = [
            1337 => [
                'CategorieIndividuEvt' => 'VISITEUR',
                'ZL_PROFIL' => 'SALON',
            ],
            9966 => [
                'CategorieIndividuEvt' => 'VISITEUR',
                'ZL_PROFIL' => 'PROSPECT',
            ],
            654 => [
                'CategorieIndividuEvt' => 'PROSPECT',
                'ZL_PROFIL' => null,
            ],
            1324 => [
                'ZL_SOUSCATEGORIE' => 1324,
            ],
            879 => [
                'CategorieIndividuEvt' => 'TYPE-NOT-EXISTS',
            ],
        ];


        $result = $this->typeConverter->convert(
            [
                $type1337->reveal(),
                $type9966->reveal(),
                $type654->reveal(),
                $type1324->reveal(),
            ],
            $mapping,
            $payload
        );

        $this->assertEquals(null === $typeId ? null : $typesById[$typeId], $result);
    }

    public function dataProvider()
    {
        return [
            'type9966' => [
                'payload' => [
                    'Id' => 12334,
                    'CategorieIndividuEvt' => 'VISITEUR',
                    'ZL_PROFIL' => 'PROSPECT',
                ],
                'expectedResult' => 9966,
            ],
            'type1337' => [
                'payload' => [
                    'Id' => 12334,
                    'CategorieIndividuEvt' => 'VISITEUR',
                    'ZL_PROFIL' => 'SALON',
                ],
                'expectedResult' => 1337,
            ],
            'type654' => [
                'payload' => [
                    'Id' => 12334,
                    'CategorieIndividuEvt' => 'PROSPECT',
                    'ZL_PROFIL' => null,
                ],
                'expectedResult' => 654,
            ],
            'type1324' => [
                'payload' => [
                    'Id' => 12334,
                    'ZL_SOUSCATEGORIE' => '1324',
                ],
                'expectedResult' => 1324,
            ],
            'type1324WithArrayValue' => [
                'payload' => [
                    'ZL_SOUSCATEGORIE' => ['1324'],
                ],
                'expectedResult' => 1324,
            ],
            'whateverIsNull' => [
                'payload' => [
                    'Id' => 12334,
                    'ZL_SOUSCATEGORIE' => 'whatever',
                ],
                'expectedResult' => null,
            ],
            'typeNotExists' => [
                'payload' => [
                    'Id' => 12334,
                    'CategorieIndividuEvt' => 'TYPE-NOT-EXISTS',
                ],
                'expectedResult' => null,
            ],
            'payloadIsEmpty' => [
                'payload' => [],
                'expectedResult' => null,
            ],
            'allFieldAreNull' => [
                'payload' => [
                    'Id' => null,
                    'CategorieIndividuEvt' => null,
                    'ZL_PROFIL' => null,
                ],
                'expectedResult' => null,
            ],
            'notKnownKey' => [
                'payload' => [
                    'notKnownKey' => 'VISITEUR',
                ],
                'expectedResult' => null,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForConvertionWithCondition
     */
    public function testConvertWithCondition(array $payload, ?int $typeId)
    {
        $type42 = $this->prophesize(Type::class);
        $type42->getId()->willReturn(42);

        $type1337 = $this->prophesize(Type::class);
        $type1337->getId()->willReturn(1337);

        $typesById = [
            42 => $type42->reveal(),
            1337 => $type1337->reveal(),
        ];

        $mapping = [
            42 => ['condition' => "CategorieIndividuEvt === 'EXPOSANT'"],
            1337 => ['condition' => "CategorieIndividuEvt === 'VISITEUR'"],
        ];

        $result = $this->typeConverter->convert(
            [
                $type42->reveal(),
                $type1337->reveal(),
            ],
            $mapping,
            $payload
        );

        $this->assertEquals(null === $typeId ? null : $typesById[$typeId], $result);
    }

    public function dataProviderForConvertionWithCondition()
    {
        return [
            'matchType42' => [
                'payload' => [
                    'CategorieIndividuEvt' => 'EXPOSANT',
                ],
                'expectedResult' => 42,
            ],
            'matchType1337' => [
                'payload' => [
                    'CategorieIndividuEvt' => 'VISITEUR',
                ],
                'expectedResult' => 1337,
            ],
            'doNotMatchType' => [
                'payload' => [
                    'CategorieIndividuEvt' => 'PROSPECT',
                ],
                'expectedResult' => null,
            ],
            'matchType42WithArrayValue' => [
                'payload' => [
                    'CategorieIndividuEvt' => ['EXPOSANT'],
                ],
                'expectedResult' => 42,
            ],
        ];
    }
}
