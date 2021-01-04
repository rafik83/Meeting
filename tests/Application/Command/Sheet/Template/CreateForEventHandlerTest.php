<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateForEvent;
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateForEventHandler;
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateResult;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateForEventHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');
        $create   = new CreateForEvent();
        $create->title = 'Toto';
        $create->event = $event;

        //expected
        $expectedTemplate = new SheetTemplate('Toto', [], ['fr'], 'fr', $dateTime);
        $expectedTemplate->setEvent($event);
        $expectedResult   = new CreateResult($expectedTemplate);

        // Mock
        $templateRepository = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new CreateForEventHandler($templateRepository->reveal(), $dateTime);
        $result = $handler->handle($create);

        $this->assertEquals($expectedResult, $result);
    }
}
