<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\GetUploadedObjectsTreeQuery;
use Proximum\Vimeet\Application\Query\Sheet\GetUploadedObjectsTreeQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectNodeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;

class GetUploadedObjectsTreeQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $admin = $this->prophesize(Admin::class);
        $admin->getLocale()->shouldBeCalled()->willReturn('fr');
        $type = $this->prophesize(Type::class);

        $object1 = $this->prophesize(UploadObject::class);
        $object1->isCrypted()->shouldBeCalled()->willReturn(true);
        $object1->getKey()->shouldBeCalled()->willReturn('Mb7d3M765e');
        $object1->getLabel('fr')->shouldBeCalled()->willReturn('Label 1');
        $object2 = $this->prophesize(UploadObject::class);
        $object2->isCrypted()->shouldBeCalled()->willReturn(false);
        $object2->getKey()->shouldBeCalled()->willReturn('Med79Mea70');
        $object2->getLabel('fr')->shouldBeCalled()->willReturn('Label 2');

        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getObjects()
            ->shouldBeCalled()
            ->willReturn([$object1->reveal(), $object2->reveal()]);

        $templateDataFactory->createRegistrationFromType($type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData->reveal());

        $sheet1RegistrationData = [
            'Mb7d3M765e' => [
                'path' => '/path/to/file1',
                'extension' => 'jpg',
            ],
            'Med79Mea70' => [],
        ];

        $participant = $this->prophesize(Participant::class);
        $participant->getIdAndFullName()->shouldBeCalled()->willReturn('1-mathieu-marchois');
        $participant->getData()
            ->shouldBeCalled()
            ->willReturn([
                'Mb7d3M765e' => [],
                'Med79Mea70' => [
                    'path' => '/path/to/file2',
                    'extension' => 'jpg',
                ]
            ]);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getIdAndFullName()->shouldBeCalled()->willReturn('2-richard-hanna');
        $participant2->getData()
            ->shouldBeCalled()
            ->willReturn([
                'Med79Mea70' => [
                    'path' => '/path/to/file3',
                    'extension' => 'jpg',
                ]
            ]);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn('1');
        $sheet1->getTitle()->shouldBeCalled()->willReturn('Title 1');
        $sheet1->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet1->getRegistrationData()->shouldBeCalled()->willReturn($sheet1RegistrationData);
        $sheet1->getParticipantsArray()->shouldBeCalled()->willReturn([]);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn('2');
        $sheet2->getTitle()->shouldBeCalled()->willReturn('Title 2');
        $sheet2->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet2->getRegistrationData()->shouldBeCalled()->willReturn([]);
        $sheet2->getParticipantsArray()->shouldBeCalled()->willReturn([$participant->reveal(), $participant2->reveal()]);

        $handler = new GetUploadedObjectsTreeQueryHandler($templateDataFactory->reveal());

        $node1 = new UploadedObjectNodeView('Mb7d3M765e', 'Label 1');
        $node1->addUploadedObjectView(new UploadedObjectView('/path/to/file1', '1-title-1.jpg', true));
        $node2 = new UploadedObjectNodeView('Med79Mea70', 'Label 2');
        $node2->addUploadedObjectView(new UploadedObjectView('/path/to/file2', '2-title-2-1-mathieu-marchois.jpg', false));
        $node2->addUploadedObjectView(new UploadedObjectView('/path/to/file3', '2-title-2-2-richard-hanna.jpg', false));

        $expectedResult = new UploadedObjectsTreeView();
        $expectedResult->addNode($node1, 'Mb7d3M765e');
        $expectedResult->addNode($node2, 'Med79Mea70');

        $result = $handler->handle(
            new GetUploadedObjectsTreeQuery(
                [$sheet1->reveal(), $sheet2->reveal()],
                $admin->reveal()
            )
        );

        $this->assertEquals($expectedResult, $result);
    }
}
