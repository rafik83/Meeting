<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\TemplateData;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\TemplateData\TemplateDataDuplicator;
use Proximum\Vimeet\Application\Components\TemplateData\TemplateDataFileDuplicator;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class TemplateDataDuplicatorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $templateDataFileDuplicator;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $templateData;

    public function setUp()
    {
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->templateDataFileDuplicator = $this->prophesize(TemplateDataFileDuplicator::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->templateData = $this->prophesize(TemplateData::class);
    }

    public function testDuplicate(): void
    {
        $data = [
            ['test'],
            ['otherTest'],
            ['anotherTest']
        ];

        $this->templateData
            ->getData()
            ->willReturn($data)
        ;

        $this->templateDataFactory
            ->createFromSheet($this->sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;
        $this->templateDataFileDuplicator
            ->handle($this->templateData->reveal())
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;

        $this->sheet->setData($data)->shouldBeCalled();

        $duplicator = new TemplateDataDuplicator(
            $this->templateDataFactory->reveal(),
            $this->templateDataFileDuplicator->reveal()
        );

        $duplicator->duplicateData($this->sheet->reveal());
    }
}
