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
use Proximum\Vimeet\Domain\Sheet\SheetInfoSetter;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;

class SheetInfoSetterTest extends TestCase
{
    public function testSetSheetTitle()
    {
        $result = [
            'text1' => [
                'content' => 'did not changed'
            ],
            'text2' => [
                'content' => 'sheet title'
            ],
            'text3' => [
                'content' => 'did not changed'
            ],
            'text4' => [
                'content' => 'sheet title'
            ],
            'text5' => [
                'content' => 'did not changed'
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

        $sheetInfoSetter = new SheetInfoSetter($templateDataFactory->reveal());
        $sheetInfoSetter->setSheetTitle($sheet->reveal(), $title);
    }
}
