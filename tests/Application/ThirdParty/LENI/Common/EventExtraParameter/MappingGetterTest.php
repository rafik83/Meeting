<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\EventExtraParameter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class MappingGetterTest extends TestCase
{
    public function testGetTypesMapping()
    {
        $event = $this->prophesize(Event::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType(
                $event,
                Type::TYPE_LENI_TYPES_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $event->reveal(),
                    Type::TYPE_LENI_TYPES_MAPPING,
                    'mapping',
                    '{"327": {"CategorieIndividuEvt": "VISITEUR", "ZL_PROFIL": "SALON" }, "328": {"CategorieIndividuEvt": "VISITEUR", "ZL_PROFIL": "PROSPECT"}, "329": {"CategorieIndividuEvt": "PROSPECT", "ZL_PROFIL": null}}',
                    new \DateTime()
                )
            )
        ;

        $typesMapping = new MappingGetter($extraParameterRepository->reveal());
        $result = $typesMapping->getMapping($event->reveal(), Type::TYPE_LENI_TYPES_MAPPING);

        $expectedResult = [
            '327' => [
                'CategorieIndividuEvt' => 'VISITEUR',
                'ZL_PROFIL' => 'SALON',
            ],
            '328' => [
                'CategorieIndividuEvt' => 'VISITEUR',
                'ZL_PROFIL' => 'PROSPECT',
            ],
            '329' => [
                'CategorieIndividuEvt' => 'PROSPECT',
                'ZL_PROFIL' => null,
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetTypesMappingIsNull()
    {
        $event = $this->prophesize(Event::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType(
                $event,
                Type::TYPE_LENI_TYPES_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $typesMapping = new MappingGetter($extraParameterRepository->reveal());
        $this->assertNull($typesMapping->getMapping($event->reveal(), Type::TYPE_LENI_TYPES_MAPPING));
    }
}
