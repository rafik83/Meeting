<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Template\Create;
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateHandler;
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateResult;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $create   = new Create('fr');
        $create->title = 'Toto';

        //expected
        $expectedTemplate = new SheetTemplate('Toto', [], ['fr'], 'fr', $dateTime);
        $expectedResult   = new CreateResult($expectedTemplate);

        // Mock
        $templateRepository = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new CreateHandler($templateRepository->reveal(), $dateTime);
        $result = $handler->handle($create);

        $this->assertEquals($expectedResult, $result);
    }
}
