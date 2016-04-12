<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Sheet\Template\Create;
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateHandler;
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateResult;
use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $create   = new Create('fr');
        $create->title = 'Toto';

        //expected
        $expectedTemplate = new Template('Toto', [], ['fr'], $dateTime);
        $expectedResult   = new CreateResult($expectedTemplate);

        // Mock
        $templateRepository = $this->prophesize(TemplateRepositoryInterface::class);
        $templateRepository->add($expectedTemplate)->shouldBeCalled();

        //Handler
        $handler = new CreateHandler($templateRepository->reveal(), $dateTime);
        $result = $handler->handle($create);

        $this->assertEquals($expectedResult, $result);
    }
}
