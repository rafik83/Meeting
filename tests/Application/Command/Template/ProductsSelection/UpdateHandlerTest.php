<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Template\ProductsSelection;

use Proximum\Vimeet\Application\Command\Template\ProductsSelection\Update;
use Proximum\Vimeet\Application\Command\Template\ProductsSelection\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\ProductsSelectionTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ProductsSelectionTemplateFactory;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $createdAt = new \DateTime();
        $event     = new Event();
        $event->setLocales(['en', 'es']);

        $productsSelectionTemplateFactory = new ProductsSelectionTemplateFactory();
        $template = $productsSelectionTemplateFactory->createFromEvent($event, 'My template', $createdAt);
        $expectedTemplate = clone $template;

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);

        $templateDataFactory = new TemplateDataFactory($nomenclatureRepository->reveal());
        $templateData        = $templateDataFactory->create($template->getValue(), [], ['en', 'es'], 'en');
        $update              = new Update($template, $templateData);
        $update->title       = 'My title';

        $expectedTemplate->setTitle('My title');

        // mock
        $productsSelectionTemplateRepository = $this->prophesize(ProductsSelectionTemplateRepositoryInterface::class);
        $productsSelectionTemplateRepository->set($expectedTemplate)->shouldBeCalled();

        $handler = new UpdateHandler($productsSelectionTemplateRepository->reveal());
        $handler->handle($update);
    }
}
