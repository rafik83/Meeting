<?php

namespace Proximum\Vimeet\Tests\Domain\Template\ObjectsCollectionBlock;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ObjectsCollectionBlock\SetArrayContentToBlock;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class SetArrayContentToBlockTest extends TestCase
{
    public function testSetArrayContentToBlock()
    {
        $nameData = new EditableText(
            'name-uid',
            'editable-text',
            [
                'translatable' => false,
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

        $dataFromSubmittedForm = [
            // 0 => item 0 was removed
            1 => [
                'name-uid' => ['content' => 'Updated Name 2'],
                'description-uid' => [
                    'translationsInput' => [
                        'fr' => ['content' => 'Updated Description fr 2'],
                        'en' => ['content' => 'Description en 2'],
                    ],
                ],
                'single-nomenclature-uid' => ['item' => 'single-item-2'],
                'multiple-nomenclature-uid' => ['items' => ['multiple-item-1', 'multiple-item-2']],
            ],
            2 => [
                'name-uid' => ['content' => 'Name 3'],
                'description-uid' => [
                    'translationsInput' => [
                        'fr' => ['content' => 'Description fr 3'],
                        'en' => ['content' => 'Description en 3'],
                    ],
                ],
                'single-nomenclature-uid' => ['item' => 'single-item-1'],
                'multiple-nomenclature-uid' => ['items' => ['multiple-item-1']],
            ],
        ];

        $setArrayContentToBlock = new SetArrayContentToBlock();
        $setArrayContentToBlock($objectsCollectionBlock, $dataFromSubmittedForm);

        $this->assertEquals(
            [
                'text' => ['Updated Name 2', 'Name 3'],
            ],
            $nameData->getData()
        );

        $this->assertEquals(
            [
                'text' => [
                    'fr' => ['Updated Description fr 2', 'Description fr 3'],
                    'en' => ['Description en 2', 'Description en 3']
                ],
            ],
            $descriptionTranslatableObject->getData()
        );

        $this->assertEquals(
            [
                'items' => ['single-item-2', 'single-item-1'],
            ],
            $singleNomenclatureObject->getData()
        );

        $this->assertEquals(
            [
                'items' => [
                    ['multiple-item-1', 'multiple-item-2'],
                    ['multiple-item-1'],
                ],
            ],
            $multipleNomenclatureObject->getData()
        );
    }
}
