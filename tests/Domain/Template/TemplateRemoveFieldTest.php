<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Domain\Template\TemplateObject\Telephone;
use Proximum\Vimeet\Domain\Template\TemplateRemoveField;

class TemplateRemoveFieldTest extends TestCase
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var \DateTime */
    private $dateTime;

    /** @var Telephone */
    private $phoneObject;

    /** @var SheetTemplate */
    private $sheetTemplate;

    /** @var TemplateData */
    private $templateData;

    /** @var Block */
    private $block;

    /** @var Image */
    private $image;

    public function setUp()
    {
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->dateTime            = new \DateTime('2017-12-01 10:00:00');
        $this->phoneObject         = new Telephone('phone', 'telephone', ['tags' => ['participant_mobile']], 'fr', 'fr');
        $this->image               = new Image('image', 'image', ['products' => [1, 2]], 'fr', 'fr');
        $this->sheetTemplate       = new SheetTemplate('title', [], ['fr'], 'fr', $this->dateTime, []);
        $this->templateData        = new TemplateData('root', [], 'fr', 'fr');
        $this->block               = new Block(12, [], 'fr', 'fr');
    }

    public function testRemoveProducts()
    {
        $this->block->addChild(0, 'a34e56d', $this->phoneObject);
        $this->block->addChild(0, '12345', $this->image);
        $this->templateData->addChild(0, 'a34e56d', $this->block);

        $this->templateDataFactory
            ->createFromTemplate($this->sheetTemplate)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        $templater = new TemplateRemoveField($this->templateDataFactory->reveal());

        $expected = [
            'a34e56d' => [
                'component' => 'block',
                'type'      => 12,
                'config'    => [],
                'children'  => [
                    [
                        'a34e56d' => [
                            'component' => 'object',
                            'type'      => 'telephone',
                            'config'    => [
                                'tags' => ['participant_mobile'],
                            ],
                        ],
                        '12345' => [
                            'component' => 'object',
                            'type'      => 'image',
                            'config'    => [
                                'products' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $templater->remove($this->sheetTemplate, 'products', []));
    }

    public function testRemoveTags()
    {
        $this->block->addChild(0, 'a34e56d', $this->phoneObject);
        $this->block->addChild(0, '12345', $this->image);
        $this->templateData->addChild(0, 'a34e56d', $this->block);

        $this->templateDataFactory
            ->createFromTemplate($this->sheetTemplate)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        $templater = new TemplateRemoveField($this->templateDataFactory->reveal());

        $expected = [
            'a34e56d' => [
                'component' => 'block',
                'type'      => 12,
                'config'    => [],
                'children'  => [
                    [
                        'a34e56d' => [
                            'component' => 'object',
                            'type'      => 'telephone',
                            'config'    => [
                                'tags' => [],
                            ],
                        ],
                        '12345' => [
                            'component' => 'object',
                            'type'      => 'image',
                            'config'    => [
                                'products' => [
                                    0 => 1,
                                    1 => 2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $templater->remove($this->sheetTemplate, 'tags', []));
    }
}
