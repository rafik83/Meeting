<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateFilledFilter;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;

class TemplateFilledFilterTest extends TestCase
{
    public function testGetFilledFilterLabel(): void
    {
        $uploadObject1 = $this->prophesize(UploadObject::class);
        $uploadObject2 = $this->prophesize(UploadObject::class);
        $uploadObject3 = $this->prophesize(UploadObject::class);

        $uploadObject1->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject1->getKey()->shouldBeCalled()->willReturn('211b2168');
        $uploadObject1->getFilterLabel()->shouldBeCalled()->willReturn('label 1');

        $uploadObject2->getFilterLabel()->shouldBeCalled()->willReturn('label 2');
        $uploadObject2->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject2->getKey()->shouldBeCalled()->willReturn('0aea62b2');

        $uploadObject3->getFilterLabel()->shouldBeCalled()->willReturn('label 3');
        $uploadObject3->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject3->getKey()->shouldBeCalled()->willReturn('ec74be5e');

        $objects = [
            $uploadObject1->reveal(),
            $uploadObject2->reveal(),
            $uploadObject3->reveal(),
        ];

        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getObjects()->willReturn($objects);

        $expectedResult = [
            0 => [
                'key' => '211b2168',
                'value' => 'label 1',
            ],
            1 => [
                'key' => '0aea62b2',
                'value' => 'label 2',
            ],
            2 => [
                'key' => 'ec74be5e',
                'value' => 'label 3',
            ],
        ];

        $result = TemplateFilledFilter::getFilledFilterLabel($templateData->reveal());

        $this->assertSame($expectedResult, $result);
    }

    public function testGetFilledFilterValues(): void
    {
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getData()->shouldBeCalled()->willReturn([
            'ec74be5e' => [
                'path' => '/tmp/path/to/file/3',
            ],
        ]);
        $participant2 = $this->prophesize(Participant::class);
        $participant2->getData()->shouldBeCalled()->willReturn(null);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()]);

        $uploadObject1 = $this->prophesize(UploadObject::class);
        $uploadObject2 = $this->prophesize(UploadObject::class);
        $uploadObject3 = $this->prophesize(UploadObject::class);

        $uploadObject1->getPath()->shouldBeCalled()->willReturn(null);
        $uploadObject1->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(true);
        $uploadObject1->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject1->getKey()->shouldBeCalled()->willReturn('211b2168');

        $uploadObject2->getPath()->shouldBeCalled()->willReturn('/tmp/path/to/file/2');
        $uploadObject2->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(true);
        $uploadObject2->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject2->getKey()->shouldBeCalled()->willReturn('0aea62b2');

        $uploadObject3->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(false);
        $uploadObject3->hasTag(Tag::PARTICIPANT_DATA)->shouldBeCalled()->willReturn(true);
        $uploadObject3->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject3->getKey()->shouldBeCalled()->willReturn('ec74be5e');

        $objects = [
            $uploadObject1->reveal(),
            $uploadObject2->reveal(),
            $uploadObject3->reveal(),
        ];

        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getObjects()->willReturn($objects);

        $expectedResult = [
            0 => [
                'key' => '211b2168',
                'status' => 'not_filled',
            ],
            1 => [
                'key' => '0aea62b2',
                'status' => 'filled',
            ],
            2 => [
                'key' => 'ec74be5e',
                'status' => 'partly_filled',
            ],
        ];

        $result = TemplateFilledFilter::getFilledFilterValues($templateData->reveal(), $sheet->reveal());

        $this->assertSame($expectedResult, $result);
    }

    public function testGetFilledFilters(): void
    {
        $uploadObject1 = $this->prophesize(UploadObject::class);
        $uploadObject2 = $this->prophesize(UploadObject::class);
        $uploadObject3 = $this->prophesize(UploadObject::class);

        $uploadObject1->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject1->getKey()->shouldBeCalled()->willReturn('211b2168');
        $uploadObject1->getFilterLabel()->shouldBeCalled()->willReturn('label 1');

        $uploadObject2->getFilterLabel()->shouldBeCalled()->willReturn('label 2');
        $uploadObject2->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject2->getKey()->shouldBeCalled()->willReturn('0aea62b2');

        $uploadObject3->getFilterLabel()->shouldBeCalled()->willReturn('label 3');
        $uploadObject3->isFilter()->shouldBeCalled()->willReturn(true);
        $uploadObject3->getKey()->shouldBeCalled()->willReturn('ec74be5e');

        $objects = [
            $uploadObject1->reveal(),
            $uploadObject2->reveal(),
            $uploadObject3->reveal(),
        ];

        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getObjects()->willReturn($objects);

        $expectedResult = [
            0 => [
                'key' => '211b2168',
                'value' => 'label 1',
            ],
            1 => [
                'key' => '0aea62b2',
                'value' => 'label 2',
            ],
            2 => [
                'key' => 'ec74be5e',
                'value' => 'label 3',
            ],
        ];

        $result = TemplateFilledFilter::getFilledFilters([$templateData->reveal()]);

        $this->assertSame($expectedResult, $result);
    }
}
