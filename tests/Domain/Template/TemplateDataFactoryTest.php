<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Text;

class TemplateDataFactoryTest extends TestCase
{
    public function testCreate()
    {
        $template = [
            'ec74be5e' => [
                'component' => 'object',
                'type'      => 'text',
                'config'    => [
                    'content' => ['fr' => 'Lorem ipsum'],
                ],
            ],
            '211b2168' => [
                'component' => 'block',
                'type'      => '8-4',
                'config'    => [],
                'children'  => [
                    [
                        '0aea62b2' => [
                            'component' => 'object',
                            'type'      => 'editable-text',
                            'config'    => [
                                'label'       => ['fr' => 'Titre'],
                                'placeholder' => ['fr' => 'Le titre'],
                                'help'        => ['fr' => 'Ici le titre'],
                                'length'      => 100,
                                'required'    => true,
                                'translatable' => true,
                            ],
                        ],
                        'azerty' => [
                            'component' => 'object',
                            'type'      => 'nomenclature',
                            'config'    => [
                                'nomenclature' => null,
                                'objective'    => 'supply',
                                'required'     => true,
                            ],
                        ],
                    ],
                    [
                    ],
                ],
            ],
        ];

        $data = [
            'ec74be5e' => [
                'text' => ['fr' => 'Lorem ipsum ec74be5e fr', 'en' => 'Lorem ipsum ec74be5e en'],
            ],
            '0aea62b2' => [
                'text' => ['fr' => 'Lorem ipsum 0aea62b2 fr', 'en' => 'Lorem ipsum 0aea62b2 en'],
            ],
            'azerty' => [],
        ];

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $event = $this->prophesize(Event::class);

        $factory      = new TemplateDataFactory($nomenclatureRepository->reveal());
        $templateData = $factory->create($template, $data, 'fr', 'fr', $event->reveal());

        // Assert objects are created
        $objects = $templateData->getObjects();

        $this->assertCount(3, $objects);
        $this->assertArrayHasKey('ec74be5e', $objects);
        $this->assertArrayHasKey('0aea62b2', $objects);

        // Assert objects are created with the right class
        $this->assertInstanceOf(Text::class, $objects['ec74be5e']);
        $this->assertInstanceOf(EditableText::class, $objects['0aea62b2']);

        // Assert getObject($key) return the right object
        $this->assertEquals($objects['ec74be5e'], $templateData->getObject('ec74be5e'));
        $this->assertEquals($objects['0aea62b2'], $templateData->getObject('0aea62b2'));

        // Assert getEditableObjects() return editable objects
        $editableObjects = $templateData->getEditableObjects();
        $this->assertCount(2, $editableObjects);
        $this->assertArrayHasKey('0aea62b2', $editableObjects);
        $this->assertEquals($editableObjects['0aea62b2'], $templateData->getObject('0aea62b2'));
        $this->assertTrue($templateData->getObject('0aea62b2')->isEditable());

        // Assert getNomenclatureObjects return nomenclature objects
        $nomenclatureObjects = $templateData->getNomenclatureObjects();
        $this->assertCount(1, $nomenclatureObjects);
        $this->assertArrayHasKey('azerty', $nomenclatureObjects);
        $this->assertEquals($editableObjects['azerty'], $templateData->getObject('azerty'));
        $this->assertTrue($templateData->getObject('azerty')->isEditable());
        $this->assertTrue($templateData->getObject('azerty')->isSupply());
        $this->assertFalse($templateData->getObject('azerty')->isNeed());
        $this->assertTrue($templateData->getObject('azerty')->isRequired());

        // Assert normalize give back the template array
        $this->assertEquals($template, $templateData->normalize());

        // Assert getData() give back the data array
        $this->assertEquals($data, $templateData->getData());
    }

    public function testCreateWithMissingTemplate()
    {
        $template = [
            'ec74be5e' => [
                'component' => 'object',
                'type'      => 'text',
                'config'    => [
                    'content' => ['fr' => 'Lorem ipsum'],
                ],
            ],
        ];

        $data = [
            'ec74be5e' => [
                'text' => ['fr' => 'Lorem ipsum ec74be5e fr', 'en' => 'Lorem ipsum ec74be5e en'],
            ],
            '0aea62b2' => [
                'text' => ['fr' => 'Lorem ipsum 0aea62b2 fr', 'en' => 'Lorem ipsum 0aea62b2 en'],
            ],
        ];

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);
        $event = $this->prophesize(Event::class);

        $factory      = new TemplateDataFactory($nomenclatureRepository->reveal());
        $templateData = $factory->create($template, $data, 'fr', 'fr', $event->reveal());

        // Assert objects are created
        $objects = $templateData->getObjects();
        $this->assertCount(1, $objects);
        $this->assertArrayHasKey('ec74be5e', $objects);
    }
}
