<?php


namespace Proximum\Vimeet\Tests\Domain\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Catalog\GetDisplayObjectiveFilter;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class GetDisplayObjectiveFilterTest extends TestCase
{
    public function testWithNeedObjective(): void
    {
        // prepare data
        $fooNomenclature = $this->prophesize(Nomenclature::class);
        $needNomenclature = $this->prophesize(Nomenclature::class);

        $needDataItems = ['dumb data', 'dumber data'];
        $fooNomenclature->isNeed()->willReturn(false);
        $fooNomenclature->isSupply()->willReturn(false);
        $needNomenclature->isNeed()->willReturn(true);
        $needNomenclature->isSupply()->willReturn(false);
        $needNomenclature->getItems()->willReturn($needDataItems);

        $sheet = $this->prophesize(Sheet::class);
        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([$fooNomenclature->reveal(), $needNomenclature->reveal()]);

        // prophecy dependencies
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory->createFromSheet($sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($templateData->reveal());

        // run tests
        $getDisplayObjectiveFilter = new GetDisplayObjectiveFilter($templateDataFactory->reveal());
        $result = $getDisplayObjectiveFilter($sheet->reveal());

        $expected = ['need'];

        $this->assertEquals($expected, $result);
    }

    public function testWithoutNeedObjective(): void
    {
        // prepare data
        $fooNomenclature = $this->prophesize(Nomenclature::class);

        $fooNomenclature->isNeed()->willReturn(false);
        $fooNomenclature->isSupply()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([$fooNomenclature]);

        // prophecy dependencies
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory->createFromSheet($sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($templateData->reveal());

        // run tests
        $getDisplayObjectiveFilter = new GetDisplayObjectiveFilter($templateDataFactory->reveal());
        $result = $getDisplayObjectiveFilter($sheet->reveal());

        $expected = [];

        $this->assertEquals($expected, $result);
    }

    public function testWithSupplyObjective(): void
    {
        // prepare data
        $fooNomenclature = $this->prophesize(Nomenclature::class);
        $supplyNomenclature = $this->prophesize(Nomenclature::class);

        $supplyDataItems = ['dumb data', 'dumber data'];

        $fooNomenclature->isSupply()->willReturn(false);
        $fooNomenclature->isNeed()->willReturn(false);
        $supplyNomenclature->isSupply()->willReturn(true);
        $supplyNomenclature->isNeed()->willReturn(false);
        $supplyNomenclature->getItems()->willReturn($supplyDataItems);

        $sheet = $this->prophesize(Sheet::class);
        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([$fooNomenclature, $supplyNomenclature]);

        // prophecy dependencies
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory->createFromSheet($sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($templateData->reveal());

        // run tests
        $getDisplayObjectiveFilter = new GetDisplayObjectiveFilter($templateDataFactory->reveal());
        $result = $getDisplayObjectiveFilter($sheet->reveal());

        $expected = ['supply'];

        $this->assertEquals($expected, $result);
    }

    public function testWithoutSupplyObjective(): void
    {
        // prepare data
        $fooNomenclature = $this->prophesize(Nomenclature::class);

        $fooNomenclature->isSupply()->willReturn(false);
        $fooNomenclature->isNeed()->willReturn(false);

        $sheet = $this->prophesize(Sheet::class);
        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([$fooNomenclature]);

        // prophecy dependencies
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory->createFromSheet($sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($templateData->reveal());

        // run tests
        $getDisplayObjectiveFilter = new GetDisplayObjectiveFilter($templateDataFactory->reveal());
        $result = $getDisplayObjectiveFilter($sheet->reveal());

        $expected = [];

        $this->assertEquals($expected, $result);
    }
}
