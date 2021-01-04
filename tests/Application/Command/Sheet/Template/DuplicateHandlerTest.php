<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Template\Duplicate;
use Proximum\Vimeet\Application\Command\Sheet\Template\DuplicateHandler;
use Proximum\Vimeet\Application\Command\Sheet\Template\DuplicateResult;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event            = EventFactory::createEvent();
        $dateTime         = new \DateTime();
        $template         = new SheetTemplate('Toto', [], ['fr'], 'fr', $dateTime, [], $event);
        $duplicate        = new Duplicate($template, $dateTime);
        $duplicate->title = 'Machin';

        //expected
        $expectedTemplate = new SheetTemplate('Machin', [], ['fr'], 'fr', $dateTime, [], $event);
        $expectedResult   = new DuplicateResult($expectedTemplate);

        // Mock
        $sheetTemplateCloner = $this->prophesize(SheetTemplateCloner::class);
        $sheetTemplateCloner
            ->duplicate($template, $event, $duplicate->title)
            ->shouldBeCalled()
            ->willReturn($expectedTemplate);

        //Handler
        $handler = new DuplicateHandler($sheetTemplateCloner->reveal());
        $result  = $handler->handle($duplicate);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleOrganizer()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');
        $template = new SheetTemplate('Toto', [], ['fr'], 'fr', $dateTime);
        $template->setEvent($event);

        $duplicate        = new Duplicate($template, $dateTime);
        $duplicate->title = 'Machin';
        $duplicate->event = $event;

        //expected
        $expectedTemplate = new SheetTemplate('Machin', [], ['fr'], 'fr', $dateTime, [], $event);
        $expectedResult   = new DuplicateResult($expectedTemplate);

        // Mock
        $sheetTemplateCloner = $this->prophesize(SheetTemplateCloner::class);
        $sheetTemplateCloner
            ->duplicate($template, $event, $duplicate->title)
            ->shouldBeCalled()
            ->willReturn($expectedTemplate);

        //Handler
        $handler = new DuplicateHandler($sheetTemplateCloner->reveal());
        $result  = $handler->handle($duplicate);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleRemoveLinkedProduct()
    {
        $event   = EventFactory::createEvent();
        $eventTo = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');
        $eventTo->setLocales(['fr'], 'fr');

        $dateTime = new \DateTime();
        $template = new SheetTemplate('Toto', [
            'ee4f2281' => [
                    'component' => 'object',
                    'type'      => 'image',
                    'config'    => [
                            'label'       => ['en' => null, 'fr' => 'Image'],
                            'placeholder' => ['en' => null, 'fr' => ''],
                            'help'        => ['en' => null, 'fr' => ''],
                            'required'    => false,
                            'style'       => '',
                            'products'    => ['1', '2'],
                        ],
                ],
        ], ['fr'], 'fr', $dateTime, [], $event);

        //expected
        $expectedTemplate = new SheetTemplate('DuplicateWithoutProduct', [
            'ee4f2281' => [
                    'component' => 'object',
                    'type'      => 'image',
                    'config'    => [
                            'label'       => ['en' => null, 'fr' => 'Image'],
                            'placeholder' => ['en' => null, 'fr' => ''],
                            'help'        => ['en' => null, 'fr' => ''],
                            'required'    => false,
                            'style'       => '',
                            'products'    => [],
                        ],
                ],
        ], ['fr'], 'fr', $dateTime, [], $eventTo);
        $expectedResult = new DuplicateResult($expectedTemplate);

        // Command
        $duplicate        = new Duplicate($template, $dateTime);
        $duplicate->title = 'DuplicateWithoutProduct';
        $duplicate->event = $eventTo;

        // Mock
        $sheetTemplateCloner = $this->prophesize(SheetTemplateCloner::class);
        $sheetTemplateCloner
            ->duplicate($template, $event, $duplicate->title)
            ->shouldBeCalled()
            ->willReturn($expectedTemplate);

        //Handler
        $handler = new DuplicateHandler($sheetTemplateCloner->reveal());
        $result  = $handler->handle($duplicate);

        $this->assertEquals($expectedResult, $result);
    }
}
