<?php

namespace Proximum\Vimeet\Tests\Application\Components\TemplateData;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\TemplateData\TemplateDataFileDuplicator;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;

class TemplateDataFileDuplicatorTest extends TestCase
{
    public function testHandle(): void
    {
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        $text = $this->prophesize(EditableText::class);
        $image1 = $this->prophesize(Image::class);
        $image2 = $this->prophesize(Image::class);
        $image3 = $this->prophesize(Image::class);

        $image1->getContentValue()->shouldBeCalled()->willReturn('');
        $image2->getContentValue()->shouldBeCalled()->willReturn('/tmp/path/to/file/2');
        $image2->hasTag(Tag::SHEET_LOGO)->shouldBeCalled()->willReturn(false);
        $image3->getContentValue()->shouldBeCalled()->willReturn('/tmp/path/to/file/3');
        $image3->hasTag(Tag::SHEET_LOGO)->shouldBeCalled()->willReturn(true);

        $objects = [
            $text->reveal(),
            $image1->reveal(),
            $image2->reveal(),
            $image3->reveal(),
        ];

        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getObjects()->willReturn($objects);

        $fileStorage->copyAndRename('/tmp/path/to/file/3')->shouldBeCalled()->willReturn('/tmp/other/path/to/file/3');
        $image3->setContentValue('/tmp/other/path/to/file/3')->shouldBeCalled();

        $duplicator = new TemplateDataFileDuplicator($fileStorage->reveal());
        $duplicator->handle($templateData->reveal());
    }
}
