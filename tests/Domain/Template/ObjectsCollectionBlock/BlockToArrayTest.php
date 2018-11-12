<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Template\ObjectsCollectionBlock;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ObjectsCollectionBlock\BlockToArray;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class BlockToArrayTest extends TestCase
{
    public function testBlockToArray()
    {
        $nameData = new EditableText(
            'name-uid',
            'editable-text',
            [
                'tags' => ['sheet_data'],
            ],
            'fr',
            'fr'
        );
        $nameData->setData(['text' => ['Name 1', 'Name 2']]);

        $descriptionTranslatableObject = new EditableText(
            'description-uid',
            'editable-text',
            [
                'translatable' => true,
                'tags' => ['sheet_data'],
            ],
            'fr',
            'fr'
        );
        $descriptionTranslatableObject->setData(
            [
                'text' => [
                    'fr' => ['Description fr 1', 'Description fr 2'],
                    'en' => ['Description en 1', 'Description en 2'],
                ],
            ]
        );

        $singleNomenclatureObject = new Nomenclature(
            'single-nomenclature-uid',
            'nomenclature',
            [
                'tags' => ['sheet_data'],
                'mode' => 'singles',
            ],
            'fr',
            'fr'
        );
        $singleNomenclatureObject->setData(
            [
                'items' => ['single-item-1', 'single-item-2'],
            ]
        );

        $multipleNomenclatureObject = new Nomenclature(
            'multiple-nomenclature-uid',
            'nomenclature',
            [
                'tags' => ['sheet_data'],
                'mode' => 'checkboxes',
            ],
            'fr',
            'fr'
        );
        $multipleNomenclatureObject->setData(
            [
                'items' => [['multiple-item-1', 'multiple-item-2'], ['multiple-item-2']],
            ]
        );

        $objectsCollectionBlock = new Block('objects_collection', [], 'fr', 'fr');
        $objectsCollectionBlock->addChild(1, 'description-uid', $descriptionTranslatableObject);
        $objectsCollectionBlock->addChild(1, 'name-uid', $nameData);
        $objectsCollectionBlock->addChild(1, 'single-nomenclature-uid', $singleNomenclatureObject);
        $objectsCollectionBlock->addChild(1, 'multiple-nomenclature-uid', $multipleNomenclatureObject);

        $blockToArray = new BlockToArray();

        $this->assertEquals(
            [
                [
                    'name-uid' => ['content' => 'Name 1'],
                    'description-uid' => ['content' => 'Description fr 1'],
                    'single-nomenclature-uid' => ['item' => 'single-item-1'],
                    'multiple-nomenclature-uid' => ['items' => ['multiple-item-1', 'multiple-item-2']],
                ],
                [
                    'name-uid' => ['content' => 'Name 2'],
                    'description-uid' => ['content' => 'Description fr 2'],
                    'single-nomenclature-uid' => ['item' => 'single-item-2'],
                    'multiple-nomenclature-uid' => ['items' => ['multiple-item-2']],
                ]
            ],
            $blockToArray($objectsCollectionBlock)
        );
    }
}
