<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Tests\Application\Template\Sheet;


use Prophecy\Argument;
use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Object\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SheetTemplateClonerTest extends \PHPUnit_Framework_TestCase
{
    public function testDuplicate()
    {
        $event         = new Event();
        $dateTime      = new \DateTimeImmutable();
        $sheetTemplate = new SheetTemplate('foobar', [
            'poiuyt' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [],
                'children'  => [
                    [
                        'azerty' => [
                            'component' => 'object',
                            'type'      => 'editable-text',
                            'config'    => [],
                        ]
                    ],
                ]
            ]
        ], ['fr'], 'fr', $dateTime);

        $sheetTemplateRepository = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateDataFactory     = $this->prophesize(TemplateDataFactory::class);
        $nomenclatureCloner      = $this->prophesize(NomenclatureCloner::class);

        $cloner = new SheetTemplateCloner(
            $sheetTemplateRepository->reveal(),
            $templateDataFactory->reveal(),
            $nomenclatureCloner->reveal(),
            $dateTime
        );

        $clone = new SheetTemplate('clone', [
            'poiuyt' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [],
                'children'  => [
                    [
                        'azerty' => [
                            'component' => 'object',
                            'type'      => 'editable-text',
                            'config'    => [],
                        ]
                    ],
                ]
            ]
        ], ['fr'], 'fr', $dateTime);
        $clone->setEvent($event);

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block        = new Block('12', [], 'fr', 'fr');
        $block->addChild(0, 'azerty', new EditableText('editable-text', [], 'fr', 'fr'));
        $templateData->addChild(0, 'poiuyt', $block);

        $templateDataFactory->createFromSheetTemplate(Argument::that(function (SheetTemplate $sheetTemplate) use ($clone) {
            $this->assertEquals($clone->getTitle(), $sheetTemplate->getTitle());
            $this->assertEquals($clone->getValue(), $sheetTemplate->getValue());

            return true;
        }))->shouldBeCalled()->willReturn($templateData);

        $nomenclatureCloner->duplicate()->shouldNotBeCalled();
        $nomenclatureCloner->duplicateIfNotExists()->shouldNotBeCalled();

        $sheetTemplateRepository->add($clone)->shouldBeCalled();

        $this->assertEquals($clone, $cloner->duplicate($sheetTemplate, $event, 'clone'));
    }
}
