<?php

namespace Proximum\Vimeet\Tests\Application\Template\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Template\SheetTemplateUpdatedEvent;
use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature as NomenclatureObject;
use Proximum\Vimeet\Domain\Template\TemplateRemoveField;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SheetTemplateClonerTest extends TestCase
{
    public function testDuplicate()
    {
        $event         = EventFactory::createEvent();
        $dateTime      = new \DateTimeImmutable();
        $sheetTemplate = new SheetTemplate('foobar', [
            'poiuyt' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [],
                'children'  => [
                    [
                        'azerty' => [
                            'component' => 'object',
                            'type'      => 'editable-text',
                            'config'    => [],
                        ],
                    ],
                ],
            ],
        ], ['fr'], 'fr', $dateTime, [], $event);

        $sheetTemplateRepository = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateDataFactory     = $this->prophesize(TemplateDataFactory::class);
        $nomenclatureCloner      = $this->prophesize(NomenclatureCloner::class);
        $templateRemoveField     = $this->prophesize(TemplateRemoveField::class);
        $delayedEventDispatcher  = $this->prophesize(DelayedEventDispatcherInterface::class);

        $cloner = new SheetTemplateCloner(
            $sheetTemplateRepository->reveal(),
            $templateDataFactory->reveal(),
            $nomenclatureCloner->reveal(),
            $dateTime,
            $templateRemoveField->reveal(),
            $delayedEventDispatcher->reveal()
        );

        $clone = new SheetTemplate('clone', [
            'poiuyt' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [],
                'children'  => [
                    [
                        'azerty' => [
                            'component' => 'object',
                            'type'      => 'editable-text',
                            'config'    => [],
                        ],
                    ],
                ],
            ],
        ], ['fr'], 'fr', $dateTime, [], $event);

        $templateRemoveField->remove()->shouldNotBeCalled();

        $nomenclatureCloner->duplicate()->shouldNotBeCalled();
        $nomenclatureCloner->duplicateIfNotExists()->shouldNotBeCalled();

        $sheetTemplateRepository->add($clone)->shouldBeCalled();

        $delayedEventDispatcher
            ->dispatch(
                Events::SHEET_TEMPLATE_UPDATED,
                new SheetTemplateUpdatedEvent($clone)
            )
            ->shouldBeCalled()
        ;

        $this->assertEquals($clone, $cloner->duplicate($sheetTemplate, $event, 'clone'));
    }

    public function testDuplicateWithNomenclature()
    {
        $event         = EventFactory::createEvent();
        $dateTime      = new \DateTimeImmutable();
        $sheetTemplate = new SheetTemplate('foobar', [
            'poiuyt' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [],
                'children'  => [
                    [
                        'azerty' => [
                            'component' => 'object',
                            'type'      => 'nomenclature',
                            'config'    => [
                                'nomenclature' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ], ['fr'], 'fr', $dateTime);

        $nomenclature = new Nomenclature('nomenclature', 1, [], true);
        $nomenclatureClone = new Nomenclature('nomenclature', 1, [], true, $event, $nomenclature);

        $sheetTemplateRepository = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateDataFactory     = $this->prophesize(TemplateDataFactory::class);
        $nomenclatureCloner      = $this->prophesize(NomenclatureCloner::class);
        $templateRemoveField     = $this->prophesize(TemplateRemoveField::class);
        $delayedEventDispatcher  = $this->prophesize(DelayedEventDispatcherInterface::class);

        $cloner = new SheetTemplateCloner(
            $sheetTemplateRepository->reveal(),
            $templateDataFactory->reveal(),
            $nomenclatureCloner->reveal(),
            $dateTime,
            $templateRemoveField->reveal(),
            $delayedEventDispatcher->reveal()
        );

        $clone = new SheetTemplate('clone', [
            'poiuyt' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [],
                'children'  => [
                    [
                        'azerty' => [
                            'component' => 'object',
                            'type'      => 'nomenclature',
                            'config'    => [
                                'nomenclature' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ], ['fr'], 'fr', $dateTime);
        $clone->setEvent($event);

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block        = new Block('12', [], 'fr', 'fr');
        $object       = new NomenclatureObject('69b3cde1', 'nomenclature', [], 'fr', 'fr');
        $object->setNomenclature($nomenclature);
        $block->addChild(0, 'azerty', $object);
        $templateData->addChild(0, 'poiuyt', $block);

        $templateDataFactory->createFromTemplate(Argument::that(function (SheetTemplate $sheetTemplate) use ($clone) {
            $this->assertEquals($clone->getTitle(), $sheetTemplate->getTitle());

            return true;
        }))->shouldBeCalled()->willReturn($templateData);

        $templateRemoveField->remove($clone, 'products', [])->shouldBeCalled()->willReturn([
            'poiuyt' => [
                'component' => 'block',
                'type'      => '12',
                'config'    => [],
                'children'  => [
                    [
                        'azerty' => [
                            'component' => 'object',
                            'type'      => 'nomenclature',
                            'config'    => [
                                'nomenclature' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $nomenclatureCloner->duplicate()->shouldNotBeCalled();
        $nomenclatureCloner->duplicateIfNotExists($nomenclature, $event)->shouldBeCalled()->willReturn($nomenclatureClone);

        $sheetTemplateRepository->add($clone)->shouldBeCalled();

        $delayedEventDispatcher
            ->dispatch(
                Events::SHEET_TEMPLATE_UPDATED,
                new SheetTemplateUpdatedEvent($clone)
            )
            ->shouldBeCalled()
        ;

        $this->assertEquals($clone, $cloner->duplicate($sheetTemplate, $event, 'clone'));
    }
}
