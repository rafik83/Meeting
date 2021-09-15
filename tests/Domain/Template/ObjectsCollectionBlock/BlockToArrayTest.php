<?php

namespace Proximum\Vimeet\Tests\Domain\Template\ObjectsCollectionBlock;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ObjectsCollectionBlock\BlockToArray;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class BlockToArrayTest extends TestCase
{
    private $nameData;
    private $descriptionTranslatableObject;
    private $singleNomenclatureObject;
    private $multipleNomenclatureObject;
    private $objectsCollectionBlock;

    public function setUp()
    {
        $this->nameData = new EditableText(
            'name-uid',
            'editable-text',
            [
                'tags' => ['sheet_data'],
            ],
            'fr',
            'fr'
        );

        $this->descriptionTranslatableObject = new EditableText(
            'description-uid',
            'editable-text',
            [
                'translatable' => true,
                'tags' => ['sheet_data'],
            ],
            'fr',
            'fr'
        );

        $this->singleNomenclatureObject = new Nomenclature(
            'single-nomenclature-uid',
            'nomenclature',
            [
                'tags' => ['sheet_data'],
                'mode' => 'singles',
            ],
            'fr',
            'fr'
        );

        $this->multipleNomenclatureObject = new Nomenclature(
            'multiple-nomenclature-uid',
            'nomenclature',
            [
                'tags' => ['sheet_data'],
                'mode' => 'checkboxes',
            ],
            'fr',
            'fr'
        );

        $this->objectsCollectionBlock = new Block('objects_collection', [], 'fr', 'fr');
        $this->objectsCollectionBlock->addChild(1, 'description-uid', $this->descriptionTranslatableObject);
        $this->objectsCollectionBlock->addChild(1, 'name-uid', $this->nameData);
        $this->objectsCollectionBlock->addChild(1, 'single-nomenclature-uid', $this->singleNomenclatureObject);
        $this->objectsCollectionBlock->addChild(1, 'multiple-nomenclature-uid', $this->multipleNomenclatureObject);
    }

    public function testBlockToArrayReturnExpectedData()
    {
        $this->nameData->setData(['text' => ['Name 1', 'Name 2']]);
        $this->descriptionTranslatableObject->setData(
            [
                'text' => [
                    'fr' => ['Description fr 1', 'Description fr 2'],
                    'en' => ['Description en 1', 'Description en 2'],
                ],
            ]
        );
        $this->singleNomenclatureObject->setData(
            [
                'items' => ['single-item-1', 'single-item-2'],
            ]
        );
        $this->multipleNomenclatureObject->setData(
            [
                'items' => [['multiple-item-1', 'multiple-item-2'], ['multiple-item-2']],
            ]
        );

        $blockToArray = new BlockToArray();

        $this->assertEquals(
            [
                [
                    'name-uid' => ['content' => 'Name 1'],
                    'description-uid' => [
                        'translationsInput' => [
                            'fr' => ['content' => 'Description fr 1'],
                            'en' => ['content' => 'Description en 1'],
                        ],
                    ],
                    'single-nomenclature-uid' => ['item' => 'single-item-1'],
                    'multiple-nomenclature-uid' => ['items' => ['multiple-item-1', 'multiple-item-2']],
                ],
                [
                    'name-uid' => ['content' => 'Name 2'],
                    'description-uid' => [
                        'translationsInput' => [
                            'fr' => ['content' => 'Description fr 2'],
                            'en' => ['content' => 'Description en 2'],
                        ],
                    ],
                    'single-nomenclature-uid' => ['item' => 'single-item-2'],
                    'multiple-nomenclature-uid' => ['items' => ['multiple-item-2']],
                ]
            ],
            $blockToArray($this->objectsCollectionBlock)
        );
    }

    public function testBlockToArrayReturnEmptyData()
    {
        $blockToArray = new BlockToArray();

        $this->assertEquals(
            [
                [
                    'name-uid' => null,
                    'description-uid' => null,
                    'single-nomenclature-uid' => null,
                    'multiple-nomenclature-uid' => null,
                ],
            ],
            $blockToArray($this->objectsCollectionBlock)
        );
    }
}
