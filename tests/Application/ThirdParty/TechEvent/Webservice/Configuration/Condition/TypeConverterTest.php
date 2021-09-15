<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\TechEvent\Webservice\Configuration\Condition;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Configuration\Condition\TypeConverter;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Infrastructure\Adapter\ExpressionLanguageAdapter;

class TypeConverterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $typeConverter;

    public function setUp(): void
    {
        $expressionLanguage = new ExpressionLanguageAdapter();
        $this->typeConverter = new TypeConverter($expressionLanguage);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTypeConverter(array $payload, ?int $typeId): void
    {
        $type1 = $this->prophesize(Type::class);
        $type1->getId()->willReturn(1);

        $type42 = $this->prophesize(Type::class);
        $type42->getId()->willReturn(42);

        $type1337 = $this->prophesize(Type::class);
        $type1337->getId()->willReturn(1337);

        $typesById = [
            1 => $type1->reveal(),
            42 => $type42->reveal(),
            1337 => $type1337->reveal(),
        ];

        $mapping = [
            1 => [
                'condition' => "IdCategory === 'J' & Country === 'FR'",
                'mapping' => [ // optional here
                   'Email' => 'email',
                ],
            ],
            42 => [
                'condition' => "IdCategory === 'J'",
                'mapping' => [ // optional here
                    'Email' => 'email',
                ],
            ],
            1337 => [
                'condition' => "IdCategory === 'U'",
                'mapping' => [ // optional here
                    'Email' => 'email',
                ],
            ],
        ];

        $result = $this->typeConverter->convert($typesById, $mapping, $payload);

        $this->assertEquals(null === $typeId ? null : $typesById[$typeId], $result);
    }

    public function dataProvider(): array
    {
        return [
            'matchType42' => [
                'payload' => [
                    'IdCategory' => 'J',
                    'Country' => 'EN'
                ],
                'expectedResult' => 42,
            ],
            'matchType1337' => [
                'payload' => [
                    'IdCategory' => 'U',
                    'Country' => 'EN'
                ],
                'expectedResult' => 1337,
            ],
            'doNotMatchType' => [
                'payload' => [
                    'IdCategory' => 'Z',
                    'Country' => 'EN'
                ],
                'expectedResult' => null,
            ],
            'matchType1' => [
                'payload' => [
                    'IdCategory' => 'J',
                    'Country' => 'FR'
                ],
                'expectedResult' => 1,
            ],
        ];
    }
}
