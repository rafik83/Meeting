<?php

namespace Proximum\Vimeet\Tests\Application\Template\Registration;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature as NomenclatureObject;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RegistrationTemplateClonerTest extends TestCase
{
    public function testDuplicate()
    {
        $event         = EventFactory::createEvent();
        $dateTime      = new \DateTimeImmutable();
        $sheetTemplate = new RegistrationTemplate('foobar', [
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
        ], ['fr'], 'fr', $dateTime);

        $sheetTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $templateDataFactory     = $this->prophesize(TemplateDataFactory::class);
        $nomenclatureCloner      = $this->prophesize(NomenclatureCloner::class);
        $eventDispatcher         = $this->prophesize(DelayedEventDispatcher::class);

        $cloner = new RegistrationTemplateCloner(
            $sheetTemplateRepository->reveal(),
            $templateDataFactory->reveal(),
            $nomenclatureCloner->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );

        $clone = new RegistrationTemplate('clone', [
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
        ], ['fr'], 'fr', $dateTime);
        $clone->setEvent($event);

        $templateData = new TemplateData('root', [], 'fr', 'fr');
        $block        = new Block('12', [], 'fr', 'fr');
        $block->addChild(0, 'azerty', new EditableText('69b3cde1', 'editable-text', [], 'fr', 'fr'));
        $templateData->addChild(0, 'poiuyt', $block);

        $templateDataFactory->createFromTemplate(Argument::that(function (RegistrationTemplate $sheetTemplate) use ($clone) {
            $this->assertEquals($clone->getTitle(), $sheetTemplate->getTitle());
            $this->assertEquals($clone->getValue(), $sheetTemplate->getValue());

            return true;
        }))->shouldBeCalled()->willReturn($templateData);

        $nomenclatureCloner->duplicate()->shouldNotBeCalled();
        $nomenclatureCloner->duplicateIfNotExists()->shouldNotBeCalled();

        $sheetTemplateRepository->add($clone)->shouldBeCalled();

        $this->assertEquals($clone, $cloner->duplicate($sheetTemplate, $event, 'clone'));
    }

    public function testDuplicateWithNomenclature()
    {
        $event         = EventFactory::createEvent();
        $dateTime      = new \DateTimeImmutable();
        $sheetTemplate = new RegistrationTemplate('foobar', [
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

        $sheetTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $templateDataFactory     = $this->prophesize(TemplateDataFactory::class);
        $nomenclatureCloner      = $this->prophesize(NomenclatureCloner::class);
        $eventDispatcher         = $this->prophesize(DelayedEventDispatcher::class);

        $cloner = new RegistrationTemplateCloner(
            $sheetTemplateRepository->reveal(),
            $templateDataFactory->reveal(),
            $nomenclatureCloner->reveal(),
            $eventDispatcher->reveal(),
            $dateTime
        );

        $clone = new RegistrationTemplate('clone', [
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

        $templateDataFactory->createFromTemplate(Argument::that(function (RegistrationTemplate $sheetTemplate) use ($clone) {
            $this->assertEquals($clone->getTitle(), $sheetTemplate->getTitle());

            return true;
        }))->shouldBeCalled()->willReturn($templateData);

        $nomenclatureCloner->duplicate()->shouldNotBeCalled();
        $nomenclatureCloner->duplicateIfNotExists($nomenclature, $event)->shouldBeCalled()->willReturn($nomenclatureClone);

        $sheetTemplateRepository->add($clone)->shouldBeCalled();

        $this->assertEquals($clone, $cloner->duplicate($sheetTemplate, $event, 'clone'));
    }
}
