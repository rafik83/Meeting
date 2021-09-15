<?php

namespace Proximum\Vimeet\Tests\Domain\Event\SheetTemplate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\SheetTemplate\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $date                       = new \DateTime();
        $eventDuplicated            = EventFactory::createEvent('event duplicated');
        $event                      = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );
        $sheetTemplate       = new SheetTemplate(
            'sheet template',
            [],
            ['fr'],
            'fr',
            $date
        );
        $clonedSheetTemplate = new SheetTemplate(
            'sheet template',
            [],
            ['fr'],
            'fr',
            $date
        );
        $reflection = new \ReflectionClass(SheetTemplate::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheetTemplate, 5);

        $templateData = new TemplateData('type', [], 'fr', 'fr');
        $sheetTemplate->setValue($templateData->getConfig());

        $sheetTemplateRepository = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $sheetTemplateRepository
            ->getTemplateForGivenEvent($eventDuplicated)
            ->shouldBeCalled()
            ->willReturn([$sheetTemplate]);

        $sheetTemplateCloner = $this->prophesize(SheetTemplateCloner::class);
        $sheetTemplateCloner
            ->duplicate($sheetTemplate, $event, $sheetTemplate->getTitle())
            ->shouldBeCalled()
            ->willReturn($clonedSheetTemplate);

        $duplicatorDataStorage = (new Duplicator(
            $sheetTemplateRepository->reveal(),
            $sheetTemplateCloner->reveal()
        ))->duplicate($event, new DuplicatorDataStorage());

        $this->assertEquals($clonedSheetTemplate, $duplicatorDataStorage->sheetTemplates[5]);
    }
}
