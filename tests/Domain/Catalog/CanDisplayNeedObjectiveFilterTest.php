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
use Proximum\Vimeet\Domain\Catalog\CanDisplayNeedObjectiveFilter;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class CanDisplayNeedObjectiveFilterTest extends TestCase
{
    public function testWithNeedObjective(): void
    {
        // prepare data
        $fooNomenclature = $this->prophesize(Nomenclature::class);
        $needNomenclature = $this->prophesize(Nomenclature::class);

        $needDataItems = ['dumb data', 'dumber data'];

        $fooNomenclature->isNeed()->willReturn(false);
        $needNomenclature->isNeed()->willReturn(true);
        $needNomenclature->getItems()->willReturn($needDataItems);

        $sheet = $this->prophesize(Sheet::class);
        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([$fooNomenclature, $needNomenclature]);

        // prophecy dependencies
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory->createFromSheet($sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($templateData->reveal());

        // run tests
        $canDisplayNeedObjectiveFilter = new CanDisplayNeedObjectiveFilter($templateDataFactory->reveal());
        $result = $canDisplayNeedObjectiveFilter->isSatisfiedBy($sheet->reveal());

        $this->assertTrue($result);
    }

    public function testWithoutNeedObjective(): void
    {
        // prepare data
        $fooNomenclature = $this->prophesize(Nomenclature::class);

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
        $canDisplayNeedObjectiveFilter = new CanDisplayNeedObjectiveFilter($templateDataFactory->reveal());
        $result = $canDisplayNeedObjectiveFilter->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }
}
