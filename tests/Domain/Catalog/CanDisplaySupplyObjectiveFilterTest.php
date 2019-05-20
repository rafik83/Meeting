<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Catalog;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Catalog\CanDisplaySupplyObjectiveFilter;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class CanDisplaySupplyObjectiveFilterTest extends TestCase
{
    public function testWithSupplyObjective(): void
    {
        // prepare data
        $fooNomenclature = $this->prophesize(Nomenclature::class);
        $supplyNomenclature = $this->prophesize(Nomenclature::class);

        $supplyDataItems = ['dumb data', 'dumber data'];

        $fooNomenclature->isSupply()->willReturn(false);
        $supplyNomenclature->isSupply()->willReturn(true);
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
        $canDisplaySupplyObjectiveFilter = new CanDisplaySupplyObjectiveFilter($templateDataFactory->reveal());
        $result = $canDisplaySupplyObjectiveFilter->isSatisfiedBy($sheet->reveal());

        $this->assertTrue($result);
    }

    public function testWithoutSupplyObjective(): void
    {
        // prepare data
        $fooNomenclature = $this->prophesize(Nomenclature::class);

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
        $canDisplaySupplyObjectiveFilter = new CanDisplaySupplyObjectiveFilter($templateDataFactory->reveal());
        $result = $canDisplaySupplyObjectiveFilter->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }
}
