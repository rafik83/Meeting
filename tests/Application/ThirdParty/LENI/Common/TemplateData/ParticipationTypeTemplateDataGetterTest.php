<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\TemplateData;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\TemplateData\ParticipationTypeTemplateDataGetter;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class ParticipationTypeTemplateDataGetterTest extends TestCase
{
    private $templateDataFactory;
    private $participationTypeTemplateDataGetter;

    public function setUp()
    {
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);

        $this->participationTypeTemplateDataGetter = new ParticipationTypeTemplateDataGetter(
            $this->templateDataFactory->reveal()
        );
    }

    public function testGetRegistrationTemplateDataByType()
    {
        $type = $this->prophesize(Type::class);
        $registrationTemplateData = $this->prophesize(TemplateData::class);
        $this
            ->templateDataFactory
            ->createRegistrationFromType($type->reveal(), null)
            ->shouldBeCalledTimes(1)
            ->willReturn($registrationTemplateData->reveal())
        ;

        // call the method several times
        for ($increment = 0; $increment <= 3; $increment++) {
            $this->assertEquals(
                $registrationTemplateData->reveal(),
                $this->participationTypeTemplateDataGetter->getRegistrationTemplateDataByType($type->reveal())
            );
        }
    }

    public function testGetSheetTemplateDataByType()
    {
        $type = $this->prophesize(Type::class);
        $sheetTemplateData = $this->prophesize(TemplateData::class);
        $this
            ->templateDataFactory
            ->createSheetTemplateFromType($type->reveal())
            ->shouldBeCalledTimes(1)
            ->willReturn($sheetTemplateData->reveal())
        ;

        // call the method several times
        for ($increment = 0; $increment <= 3; $increment++) {
            $this->assertEquals(
                $sheetTemplateData->reveal(),
                $this->participationTypeTemplateDataGetter->getSheetTemplateDataByType($type->reveal())
            );
        }
    }
}
