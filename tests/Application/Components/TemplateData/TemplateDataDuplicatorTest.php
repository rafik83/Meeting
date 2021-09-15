<?php

namespace Proximum\Vimeet\Tests\Application\Components\TemplateData;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\TemplateData\TemplateDataDuplicator;
use Proximum\Vimeet\Application\Components\TemplateData\TemplateDataFileDuplicator;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class TemplateDataDuplicatorTest extends TestCase
{
    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $templateDataFileDuplicator;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $templateData;

    public function setUp()
    {
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->templateDataFileDuplicator = $this->prophesize(TemplateDataFileDuplicator::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->templateData = $this->prophesize(TemplateData::class);
    }

    public function testDuplicate(): void
    {
        $data = [
            ['test'],
            ['otherTest'],
            ['anotherTest']
        ];

        $this->templateData
            ->getData()
            ->willReturn($data)
        ;

        $this->templateDataFactory
            ->createFromSheet($this->sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;
        $this->templateDataFileDuplicator
            ->handle($this->templateData->reveal())
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;

        $this->sheet->setData($data)->shouldBeCalled();

        $duplicator = new TemplateDataDuplicator(
            $this->templateDataFactory->reveal(),
            $this->templateDataFileDuplicator->reveal()
        );

        $duplicator->duplicateData($this->sheet->reveal());
    }

    public function testDuplicateWithFromAndSanitizedObjects(): void
    {
        $fromSheet = $this->prophesize(Sheet::class);
        $templateData2 = $this->prophesize(TemplateData::class);

        $object1 = $this->prophesize(Image::class);
        $object2 = $this->prophesize(Nomenclature::class);
        $object3 = $this->prophesize(MediaCollection::class);

        $object1->getTags()->willReturn([Tag::SHEET_LOGO]);
        $object2->getTags()->willReturn([]);
        $object3->getTags()->willReturn([Tag::SHEET_DESCRIPTION]);

        $object1->getData()->shouldBeCalled()->willReturn(['image' => 'toto.jpg']);
        $object2->getData()->shouldNotBeCalled();
        $object3->getData()->shouldNotBeCalled();

        $objects = [
            'key1' => $object1->reveal(),
            'key2' => $object2->reveal(),
            'key3' => $object3->reveal(),
        ];
        $templateData2->getObjects()->shouldBeCalled()->willReturn($objects);

        $data = [
            ['test'],
            ['otherTest'],
            ['anotherTest']
        ];

        $this->templateData
            ->getData()
            ->willReturn($data)
        ;

        $object11 = $this->prophesize(Image::class);
        $object12 = $this->prophesize(Nomenclature::class);
        $object11->getTags()->willReturn([Tag::SHEET_LOGO]);
        $object12->getTags()->willReturn([]);
        $objects2 = [
            $object11->reveal(),
            $object12->reveal(),
        ];

        $object11->setData(['image' => 'toto.jpg'])->shouldBeCalled();
        $object12->setData(Argument::any())->shouldNotBeCalled();

        $this->templateData->sanitizedDataWithoutType(['product'])->shouldBeCalled();
        $this->templateData->getObjects()->shouldBeCalled()->willReturn($objects2);

        $this->templateDataFactory
            ->createFromSheet($this->sheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;
        $this->templateDataFactory
            ->createFromSheet($fromSheet->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($templateData2->reveal())
        ;

        $this->templateDataFileDuplicator
            ->handle($this->templateData->reveal())
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;

        $this->sheet->setData($data)->shouldBeCalled();

        $duplicator = new TemplateDataDuplicator(
            $this->templateDataFactory->reveal(),
            $this->templateDataFileDuplicator->reveal()
        );

        $duplicator->duplicateData($this->sheet->reveal(), $fromSheet->reveal(), ['product']);
    }
}
