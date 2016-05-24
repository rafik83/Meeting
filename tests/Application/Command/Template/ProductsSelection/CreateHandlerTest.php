<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Template\ProductsSelection;

use Proximum\Vimeet\Application\Command\Template\ProductsSelection\Create;
use Proximum\Vimeet\Application\Command\Template\ProductsSelection\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Template\ProductsSelectionTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ProductsSelectionTemplateFactory;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $createdAt = new \DateTime();

        $event = new Event();
        $event->setLocales(['en', 'es']);

        $create = new Create();
        $create->title = 'My template';
        $create->event = $event;

        $productsSelectionTemplateFactory = new ProductsSelectionTemplateFactory();

        $expectedTemplate = $productsSelectionTemplateFactory->createFromEvent($event, 'My template', $createdAt);

        // mock
        $productsSelectionTemplateRepository = $this->prophesize(ProductsSelectionTemplateRepositoryInterface::class);
        $productsSelectionTemplateRepository->add($expectedTemplate)->shouldBeCalled();

        $handler = new CreateHandler(
            $productsSelectionTemplateRepository->reveal(),
            $productsSelectionTemplateFactory,
            $createdAt
        );
        $handler->handle($create);
    }
}
