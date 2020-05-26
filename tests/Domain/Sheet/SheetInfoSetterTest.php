<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Sheet\SheetInfoSetter;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;

class SheetInfoSetterTest extends TestCase
{
    public function testSetSheetTitle()
    {
        $result = [
            'text1' => [
                'content' => 'did not changed',
            ],
            'text2' => [
                'content' => 'sheet title',
            ],
            'text3' => [
                'content' => 'did not changed',
            ],
            'text4' => [
                'content' => 'sheet title',
            ],
            'text5' => [
                'content' => 'did not changed',
            ],
        ];
        $title = 'sheet title';
        $sheet = $this->prophesize(Sheet::class);
        $sheet->setRegistrationData($result)->shouldBeCalled();
        $templateData = $this->prophesize(TemplateData::class);
        $text1 = $this->prophesize(EditableText::class);
        $text2 = $this->prophesize(EditableText::class);
        $text3 = $this->prophesize(EditableText::class);
        $text4 = $this->prophesize(EditableText::class);
        $text5 = $this->prophesize(EditableText::class);
        $text1->hasTag(Tag::SHEET_TITLE)->willReturn(false);
        $text2->hasTag(Tag::SHEET_TITLE)->willReturn(true);
        $text3->hasTag(Tag::SHEET_TITLE)->willReturn(false);
        $text4->hasTag(Tag::SHEET_TITLE)->willReturn(true);
        $text5->hasTag(Tag::SHEET_TITLE)->willReturn(false);
        $text2->setContentValue($title)->shouldBeCalled();
        $text4->setContentValue($title)->shouldBeCalled();
        $templateData->getSheetData()->willReturn($result);

        $templateData->getEditableSheetDataExceptedImageObjects()->willReturn([
            $text1,
            $text2,
            $text3,
            $text4,
            $text5,
        ]);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory->createRegistrationFromSheet($sheet->reveal())->shouldBeCalled()->willReturn($templateData);

        $sheetTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $block = new Block('12', [], 'fr', 'fr');
        $editableText1 = new EditableText('69b3cde1', 'editable-text', ['tag' => 'sheet_title',], 'fr', 'fr');
        $editableText1->setData(['text' => 'Test 1']);
        $editableText2 = new EditableText('69b3cde2', 'editable-text', ['tag' => 'sheet_category',], 'fr', 'fr');
        $editableText2->setData(['text' => 'Test 2']);
        $editableText3 = new EditableText('69b3cde3', 'editable-text', ['tag' => 'sheet_organization',], 'fr', 'fr');
        $editableText3->setData(['text' => 'Test 3']);
        $block->addChild(1, '69b3cde1', $editableText1);
        $block->addChild(2, '69b3cde2', $editableText2);
        $block->addChild(3, '69b3cde3', $editableText3);
        $sheetTemplateData->addChild(1, '811f6edf', $block);
        $templateDataFactory
            ->createFromSheet($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData)
        ;

        $sheet->setData([
            '69b3cde1' => ['text' => null],
            '69b3cde2' => ['text' => 'Test 2'],
            '69b3cde3' => ['text' => null],
        ])->shouldBeCalled();

        $sheetInfoSetter = new SheetInfoSetter($templateDataFactory->reveal());
        $sheetInfoSetter->setSheetTitle($sheet->reveal(), $title);
    }
}
