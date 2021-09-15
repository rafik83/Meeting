<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Template\Block;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class BlockObjectsCollectionContentTest extends TestCase
{
    public function testGetObjectsCollectionContent()
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

        $singleNomenclatureModel = $this->prophesize(NomenclatureModel::class);
        $singleNomenclatureModel->getDepth()->shouldBeCalled()->willReturn(1);
        $singleNomenclatureModel->getId()->shouldBeCalled()->willReturn(1969);
        $singleNomenclatureModel
            ->getLastLevel()
            ->shouldBeCalled()
            ->willReturn(
                [
                    'single-item-1' => new NomenclatureItem('single-item-1', ['fr' => 'Single Item label 1']),
                    'single-item-2' => new NomenclatureItem('single-item-2', ['fr' => 'Single Item label 2']),
                ]
            )
        ;

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
        $singleNomenclatureObject->setNomenclature($singleNomenclatureModel->reveal());

        $multipleNomenclatureModel = $this->prophesize(NomenclatureModel::class);
        $multipleNomenclatureModel->getId()->shouldBeCalled()->willReturn(963);
        $multipleNomenclatureModel->getDepth()->shouldBeCalled()->willReturn(1);
        $multipleNomenclatureModel
            ->getLastLevel()
            ->shouldBeCalled()
            ->willReturn(
                [
                    'multiple-item-1' => new NomenclatureItem('multiple-item-1', ['fr' => 'Multiple label 1']),
                    'multiple-item-2' => new NomenclatureItem('multiple-item-2', ['fr' => 'Multiple label 2']),
                ]
            )
        ;

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
        $multipleNomenclatureObject->setNomenclature($multipleNomenclatureModel->reveal());

        $objectsCollectionBlock = new Block('objects_collection', [], 'fr', 'fr');
        $objectsCollectionBlock->addChild(1, 'description-uid', $descriptionTranslatableObject);
        $objectsCollectionBlock->addChild(1, 'name-uid', $nameData);
        $objectsCollectionBlock->addChild(1, 'single-nomenclature-uid', $singleNomenclatureObject);
        $objectsCollectionBlock->addChild(1, 'multiple-nomenclature-uid', $multipleNomenclatureObject);

        $this->assertEquals(
            [
                [
                    'name-uid' => 'Name 1',
                    'description-uid' => 'Description fr 1',
                    'single-nomenclature-uid' => 'Single Item label 1',
                    'multiple-nomenclature-uid' => ['Multiple label 1', 'Multiple label 2'],
                ],
                [
                    'name-uid' => 'Name 2',
                    'description-uid' => 'Description fr 2',
                    'single-nomenclature-uid' => 'Single Item label 2',
                    'multiple-nomenclature-uid' => ['Multiple label 2'],
                ]
            ],
            $objectsCollectionBlock->getObjectsCollectionContent()
        );
    }
}
