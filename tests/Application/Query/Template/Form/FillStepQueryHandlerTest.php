<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Template\Form;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Template\Form\FillStepQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FillStepQueryHandler;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQueryHandler;
use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Exception\BlockForGivenStepNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\GivenStepIsRequiredAndNotFilledException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class FillStepQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $formTemplate, $sheet, $participant, $formTemplateDataQueryHandler;

    public function setup()
    {
        $this->formTemplate = $this->prophesize(FormTemplate::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->formTemplateDataQueryHandler = $this->prophesize(FormTemplateDataQueryHandler::class);
    }

    public function testHandleBlockNotFound()
    {
        $this->expectException(BlockForGivenStepNotFoundException::class);

        $templateData = $this->prophesize(TemplateData::class);
        $this->formTemplateDataQueryHandler
            ->handle(new FormTemplateDataQuery($this->formTemplate->reveal(), $this->sheet->reveal(), $this->participant->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $templateData->getBlock(12)->shouldBeCalled()->willReturn(null);

        $query = new FillStepQuery(
            $this->formTemplate->reveal(),
            $this->sheet->reveal(),
            $this->participant->reveal(),
            'fr',
            12
        );
        $handler = new FillStepQueryHandler($this->formTemplateDataQueryHandler->reveal());
        $handler->handle($query);
    }

    public function testHandleWithRequiredStepNotFilled()
    {
        $this->expectException(GivenStepIsRequiredAndNotFilledException::class);

        $templateData = $this->prophesize(TemplateData::class);
        $this->formTemplateDataQueryHandler
            ->handle(new FormTemplateDataQuery($this->formTemplate->reveal(), $this->sheet->reveal(), $this->participant->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $block = $this->prophesize(Block::class);
        $block1 = $this->prophesize(Block::class);
        $block2 = $this->prophesize(Block::class);
        $templateData->getBlock(3)->shouldBeCalled()->willReturn($block);

        $templateData->getBlocks()->willReturn([$block1->reveal(), $block2->reveal(), $block->reveal()]);
        $object1 = $this->prophesize(EditableText::class);
        $object1->getRequired()->shouldBeCalled()->willReturn(false);

        $object2 = $this->prophesize(EditableText::class);
        $object2->getRequired()->shouldBeCalled()->willReturn(false);

        $object3 = $this->prophesize(Nomenclature::class);
        $object3->getRequired()->shouldBeCalled()->willReturn(true);
        $object3->isEmpty()->shouldBeCalled()->willReturn(true);

        $block1->getEditableObjects()->shouldBeCalled()->willReturn([$object1->reveal()]);
        $block2->getEditableObjects()->shouldBeCalled()->willReturn([$object2->reveal(), $object3->reveal()]);

        $query = new FillStepQuery(
            $this->formTemplate->reveal(),
            $this->sheet->reveal(),
            $this->participant->reveal(),
            'fr',
            3
        );
        $handler = new FillStepQueryHandler($this->formTemplateDataQueryHandler->reveal());
        $handler->handle($query);
    }

    public function testHandle()
    {
        $templateData = $this->prophesize(TemplateData::class);
        $this->formTemplateDataQueryHandler
            ->handle(new FormTemplateDataQuery($this->formTemplate->reveal(), $this->sheet->reveal(), $this->participant->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $block = $this->prophesize(Block::class);
        $block1 = $this->prophesize(Block::class);
        $block2 = $this->prophesize(Block::class);
        $templateData->getBlock(3)->shouldBeCalled()->willReturn($block);
        $templateData->getBlocksCount()->shouldBeCalled()->willReturn(5);

        $templateData->getBlocks()->willReturn([$block1->reveal(), $block2->reveal(), $block->reveal()]);
        $object1 = $this->prophesize(EditableText::class);
        $object1->getRequired()->shouldBeCalled()->willReturn(false);

        $object2 = $this->prophesize(EditableText::class);
        $object2->getRequired()->shouldBeCalled()->willReturn(false);

        $object3 = $this->prophesize(Nomenclature::class);
        $object3->getRequired()->shouldBeCalled()->willReturn(true);
        $object3->isEmpty()->shouldBeCalled()->willReturn(false);

        $block1->getEditableObjects()->shouldBeCalled()->willReturn([$object1->reveal()]);
        $block2->getEditableObjects()->shouldBeCalled()->willReturn([$object2->reveal(), $object3->reveal()]);
        $block->getEditableObjects()->shouldNotBeCalled();

        $query = new FillStepQuery(
            $this->formTemplate->reveal(),
            $this->sheet->reveal(),
            $this->participant->reveal(),
            'fr',
            3
        );
        $handler = new FillStepQueryHandler($this->formTemplateDataQueryHandler->reveal());
        $result = $handler->handle($query);

        $view = new BlockStepView($block->reveal(), 3, 5);

        $this->assertEquals($view, $result);
    }
}
